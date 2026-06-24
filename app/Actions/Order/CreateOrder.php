<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\BaseAction;
use App\Models\CutType;
use App\Models\OrderItems;
use App\Models\Orders;
use App\Models\Product;
use App\Services\UnitConversionService;

final class CreateOrder extends BaseAction
{
    // ...existing code...

    /**
     * Create order items.
     */
    private function createOrderItems(Orders $order, array $items, float $taxPercentage): void
    {
        $unitConverter = app(UnitConversionService::class);

        foreach ($items as $item) {
            $lineTax = ($item['total_price'] * $taxPercentage) / 100;

            // Get product to retrieve base pricing unit and grams per piece
            $product = Product::find($item['product_id']);
            $gramsPerPiece = $product?->grams_per_piece;
            $baseUnit = $product?->base_price_unit ?? 'kg';
            $orderedUnit = $item['weight_unit'] ?? 'kg';

            // Normalize unit price to kg for storage (backwards compatibility)
            $normalizedPrice = $unitConverter->calculatePrice(
                (float) $item['unit_price'],
                $orderedUnit,
                'kg',
                $gramsPerPiece
            );

            OrderItems::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'category_id' => $item['category_id'],
                'cut_id' => $item['cut_id'],
                'product_name' => $item['product_name'] ?? 'Product',
                'cut_name' => $item['cut_name'] ?? CutType::select('name')->where('id', $item['cut_id'])->value('name'),
                'price_per_kg' => $normalizedPrice,
                'ordered_weight' => $item['quantity'],
                'actual_weight' => $item['weight'] ?? $item['quantity'],
                'weight_unit' => $orderedUnit,
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
