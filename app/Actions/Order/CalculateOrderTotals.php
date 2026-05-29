<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\BaseAction;
use App\Models\Coupon;
use App\Models\Orders;

final class CalculateOrderTotals extends BaseAction
{
    /**
     * Execute the action to calculate order totals.
     */
    public function execute(array $data): array
    {
        $items = $data['items'] ?? [];
        $couponCode = $data['coupon_code'] ?? null;
        $customerId = $data['customer_id'] ?? null;

        $subtotal = $this->calculateSubtotal($items);
        $taxPercentage = 18.00; // TODO: Move to config
        $taxAmount = ($subtotal * $taxPercentage) / 100;

        $discountAmount = $this->calculateDiscount($subtotal, $couponCode, $customerId);

        // TODO: Implement delivery charge calculation
        $deliveryCharge = 0;

        $totalAmount = $subtotal - $discountAmount + $taxAmount + $deliveryCharge;

        return [
            'subtotal' => $subtotal,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'delivery_charge' => $deliveryCharge,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Calculate subtotal from items.
     */
    private function calculateSubtotal(array $items): float
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['total_price'] ?? 0;
        }

        return $subtotal;
    }

    /**
     * Calculate discount amount based on coupon.
     */
    private function calculateDiscount(float $subtotal, ?string $couponCode, ?int $customerId): float
    {
        if (! $couponCode || ! $customerId) {
            return 0;
        }

        $coupon = Coupon::where('code', mb_trim($couponCode))
            ->where('status', 1)
            ->first();

        if (! $coupon) {
            return 0;
        }

        $now = now();

        // Check validity dates
        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return 0;
        }

        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return 0;
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return 0;
        }

        // Check per customer usage
        if ($coupon->usage_per_customer) {
            $usageCount = Orders::where('customer_id', $customerId)
                ->where('coupon_code', $couponCode)
                ->where('is_cancelled', false)
                ->count();
            if ($usageCount >= $coupon->usage_per_customer) {
                return 0;
            }
        }

        // Calculate discount
        $discount = 0.0;

        if ($coupon->type === 1) { // Percentage
            $discount = (float) (($subtotal * (float) $coupon->value) / 100);
        } elseif ($coupon->type === 2) { // Fixed amount
            $discount = (float) $coupon->value;
        }

        // Apply max discount if set
        if ($coupon->max_discount_amount && (float) $discount > (float) $coupon->max_discount_amount) {
            $discount = (float) $coupon->max_discount_amount;
        }

        return (float) $discount;
    }
}
