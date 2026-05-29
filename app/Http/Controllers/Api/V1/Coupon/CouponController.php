<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Coupon;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Models\Coupon;
use App\Models\Orders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

final class CouponController extends ResponseController
{
    /**
     * Validate a coupon code.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $customer = Auth::guard('customer-api')->user();
        if (! $customer) {
            return $this->returnResponse([], 'Unauthorized.', 401);
        }

        // Rate limiting: 10 attempts per minute per customer
        $key = 'coupon-validate:'.$customer->id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return $this->returnResponse([], 'Too many attempts. Please try again later.', 429);
        }
        RateLimiter::hit($key);

        $code = mb_trim($request->input('code'));
        $coupon = Coupon::where('code', $code)->where('status', 1)->first();

        if (! $coupon) {
            return $this->returnResponse([], 'Invalid coupon code.', 400);
        }

        $now = now();

        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return $this->returnResponse([], 'Coupon is not yet valid.', 400);
        }

        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return $this->returnResponse([], 'Coupon has expired.', 400);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return $this->returnResponse([], 'Coupon usage limit exceeded.', 400);
        }

        // Check usage per customer if set
        if ($coupon->usage_per_customer) {
            $usageCount = Orders::where('customer_id', $customer->id)
                ->where('coupon_code', $code)
                ->where('is_cancelled', false)
                ->count();
            if ($usageCount >= $coupon->usage_per_customer) {
                return $this->returnResponse([], 'You have already used this coupon the maximum allowed times.', 400);
            }
        }

        return $this->returnResponse([
            'coupon' => $coupon->code,
            'discount' => [
                'type' => $coupon->type,
                'value' => $coupon->value,
                'max_discount' => $coupon->max_discount_amount,
            ],
        ], 'Coupon is valid.');
    }
}
