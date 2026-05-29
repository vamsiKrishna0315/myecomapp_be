<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\BaseAction;
use App\Models\CutType;
use App\Models\OrderItems;
use App\Models\Orders;

final class CreateOrder extends BaseAction
{
    /**
     * Execute the action to create a new order.
     */
    public function execute(array $data): Orders
    {
        $totals = app(CalculateOrderTotals::class)->execute($data);

        $order = $this->createOrder($data, $totals);
        $this->createOrderItems($order, $data['items'], $totals['tax_percentage']);

        return $this->afterExecution($order, $data);
    }

    /**
     * Handle after order creation.
     *
     * @param  Orders  $order
     */
    protected function afterExecution($order, array $data): Orders
    {
        $order->load(['items', 'deliveryAddress', 'billingAddress']);

        return $order;
    }

    /**
     * Create the order record.
     */
    private function createOrder(array $data, array $totals): Orders
    {
        return Orders::create([
            'customer_id' => $data['customer_id'],
            'delivery_address_id' => $data['delivery_address_id'],
            'billing_address_id' => $data['billing_address_id'] ?? $data['delivery_address_id'],
            'delivery_date' => $data['delivery_date'] ?? null,
            'delivery_time_slot' => $data['delivery_time_slot'] ?? null,
            'special_instructions' => $data['special_instructions'] ?? null,
            'subtotal' => $totals['subtotal'],
            'coupon_code' => $data['coupon_code'] ?? null,
            'discount_amount' => $totals['discount_amount'],
            'discount_type' => 1, // 1 = percentage, 2 = fixed
            'tax_percentage' => $totals['tax_percentage'],
            'tax_amount' => $totals['tax_amount'],
            'delivery_charge' => $totals['delivery_charge'],
            'total_amount' => $totals['total_amount'],
            'billing_type_id' => $data['billing_type_id'],
            'payment_status' => 0, // 0 = pending
            'status' => 1, // Active
            'current_status_id' => 1,
            'current_status_code' => 'pending',
        ]);
    }

    /**
     * Create order items.
     */
    private function createOrderItems(Orders $order, array $items, float $taxPercentage): void
    {
        foreach ($items as $item) {
            $lineTax = ($item['total_price'] * $taxPercentage) / 100;

            OrderItems::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'category_id' => $item['category_id'],
                'cut_id' => $item['cut_id'],
                'product_name' => $item['product_name'] ?? 'Product',
                'cut_name' => $item['cut_name'] ?? CutType::select('name')->where('id', $item['cut_id'])->value('name'),
                'price_per_kg' => $item['unit_price'],
                'ordered_weight' => $item['quantity'],
                'actual_weight' => $item['weight'] ?? $item['quantity'],
                'weight_unit' => $item['weight_unit'] ?? 'kg',
                'line_subtotal' => $item['total_price'],
                'line_discount' => 0,
                'line_tax' => $lineTax,
                'line_total' => $item['total_price'] + $lineTax,
                'special_instructions' => $item['special_instructions'] ?? null,
                'order_item_status' => 0,
                'status' => 1,
            ]);
        }
    }
}
