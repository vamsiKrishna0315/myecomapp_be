<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponSetting;
use App\Models\Customer;
use Illuminate\Support\Str;

final class ReferralCouponService
{
    /**
     * Get the customer's existing referral coupon, or create one.
     */
    public function getOrCreateForCustomer(Customer $customer): Coupon
    {
        $coupon = Coupon::query()
            ->where('customer_id', $customer->id)
            ->where('is_referral', true)
            ->first();

        if ($coupon) {
            return $coupon;
        }

        $settings = CouponSetting::current();

        return Coupon::create([
            'customer_id' => $customer->id,
            'is_referral' => true,
            'code' => $this->generateUniqueCode($customer),
            'type' => $settings->referral_discount_type,
            'value' => $settings->referral_discount_value,
            'min_order_amount' => $settings->referral_min_order_amount,
            'max_discount_amount' => $settings->referral_max_discount_amount,
            'usage_per_customer' => 1,
            'valid_from' => now(),
            'valid_until' => now()->addYears(5),
            'description' => "Referral coupon for {$customer->id}",
            'status' => 1,
        ]);
    }

    /**
     * Check whether a referral code is currently eligible for use.
     * Self-referral (checkoutCustomerId owns the coupon) is rejected here too,
     * whenever the checkout customer is already known.
     *
     * @return array{eligible: bool, reason?: string, coupon?: Coupon}
     */
    public function checkEligibility(string $code, ?int $checkoutCustomerId = null): array
    {
        $coupon = Coupon::where('code', mb_trim($code))
            ->where('status', 1)
            ->first();

        if (! $coupon) {
            return ['eligible' => false, 'reason' => 'Invalid coupon code.'];
        }

        $now = now();

        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return ['eligible' => false, 'reason' => 'Coupon is not yet valid.'];
        }

        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return ['eligible' => false, 'reason' => 'Coupon has expired.'];
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return ['eligible' => false, 'reason' => 'Coupon usage limit exceeded.'];
        }

        if ($coupon->is_referral && $checkoutCustomerId !== null && $coupon->customer_id === $checkoutCustomerId) {
            return ['eligible' => false, 'reason' => 'You cannot use your own referral code.'];
        }

        return ['eligible' => true, 'coupon' => $coupon];
    }

    /**
     * Get referral performance stats for a customer's own referral coupon.
     *
     * @return array{code: ?string, total_orders: int, successful_orders: int, pending_orders: int, total_discount_given: float}
     */
    public function getAnalytics(Customer $customer): array
    {
        $coupon = Coupon::query()
            ->where('customer_id', $customer->id)
            ->where('is_referral', true)
            ->first();

        if (! $coupon) {
            return [
                'code' => null,
                'total_orders' => 0,
                'successful_orders' => 0,
                'pending_orders' => 0,
                'total_discount_given' => 0.0,
            ];
        }

        $ordersUsingCode = $coupon->orders()->where('is_cancelled', false);

        $totalOrders = (clone $ordersUsingCode)->count();

        $successfulOrdersQuery = (clone $ordersUsingCode)
            ->where('payment_status', 1)
            ->whereNotNull('delivered_at');

        $successfulOrders = (clone $successfulOrdersQuery)->count();
        $totalDiscountGiven = (float) (clone $successfulOrdersQuery)->sum('discount_amount');

        return [
            'code' => $coupon->code,
            'total_orders' => $totalOrders,
            'successful_orders' => $successfulOrders,
            'pending_orders' => $totalOrders - $successfulOrders,
            'total_discount_given' => $totalDiscountGiven,
        ];
    }

    /**
     * Generate a unique, human-shareable referral code for a customer.
     */
    private function generateUniqueCode(Customer $customer): string
    {
        $base = Str::upper(Str::slug($customer->first_name ?? 'FRIEND', ''));
        $base = mb_substr($base !== '' ? $base : 'FRIEND', 0, 8);

        do {
            $code = $base.random_int(1000, 9999);
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }
}
