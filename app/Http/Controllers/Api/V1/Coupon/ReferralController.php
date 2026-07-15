<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Coupon;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Services\ReferralCouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

final class ReferralController extends ResponseController
{
    public function __construct(private readonly ReferralCouponService $referralCouponService) {}

    /**
     * Get or create the authenticated customer's referral coupon and share link.
     */
    public function generate(): JsonResponse
    {
        $customer = Auth::guard('customer-api')->user();
        if (! $customer) {
            return $this->returnResponse([], 'Unauthorized.', 401);
        }

        $coupon = $this->referralCouponService->getOrCreateForCustomer($customer);

        return $this->returnResponse([
            'code' => $coupon->code,
            'discount' => [
                'type' => $coupon->type,
                'value' => $coupon->value,
            ],
            'share_url' => mb_rtrim(config('app.frontend_url'), '/')."/new-checkout?ref={$coupon->code}",
        ], 'Referral link generated.');
    }

    /**
     * Publicly check whether a referral/coupon code is eligible for use.
     * Self-referral can only be enforced once the checkout customer is authenticated.
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $key = 'referral-check:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return $this->returnResponse([], 'Too many attempts. Please try again later.', 429);
        }
        RateLimiter::hit($key);

        $checkoutCustomerId = Auth::guard('customer-api')->id();

        $result = $this->referralCouponService->checkEligibility($request->string('code')->toString(), $checkoutCustomerId);

        if (! $result['eligible']) {
            return $this->returnResponse(['eligible' => false], $result['reason'], 400);
        }

        return $this->returnResponse([
            'eligible' => true,
            'coupon' => $result['coupon']->code,
            'discount' => [
                'type' => $result['coupon']->type,
                'value' => $result['coupon']->value,
                'max_discount' => $result['coupon']->max_discount_amount,
            ],
        ], 'Coupon is valid.');
    }

    /**
     * Get the authenticated customer's referral performance analytics.
     */
    public function analytics(): JsonResponse
    {
        $customer = Auth::guard('customer-api')->user();
        if (! $customer) {
            return $this->returnResponse([], 'Unauthorized.', 401);
        }

        return $this->returnResponse(
            $this->referralCouponService->getAnalytics($customer),
            'Referral analytics fetched.'
        );
    }
}
