<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrdersResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditOrders extends EditRecord
{
    protected static string $resource = OrdersResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove store vendor fields - they belong to store_vendor_orders table, not orders table
        unset($data['store_id'], $data['store_vendor_id'], $data['is_eligible']);

        // Ensure all order items have required fields
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $key => $item) {
                // Ensure all required fields have values
                $data['items'][$key]['product_name'] = $item['product_name'] ?? 'Product';
                $data['items'][$key]['cut_name'] = $item['cut_name'] ?? 'Cut';
                $data['items'][$key]['sku'] = $item['sku'] ?? '';
                $data['items'][$key]['category_id'] = $item['category_id'] ?? 0;
                $data['items'][$key]['price_per_piece'] = $item['price_per_piece'] ?? 0;
                $data['items'][$key]['weight_unit'] = $item['weight_unit'] ?? 'kg';
                $data['items'][$key]['preparation_style'] = $item['preparation_style'] ?? '';
                $data['items'][$key]['line_discount'] = $item['line_discount'] ?? 0;
                $data['items'][$key]['line_tax'] = $item['line_tax'] ?? 0;
                $data['items'][$key]['special_instructions'] = $item['special_instructions'] ?? '';
                $data['items'][$key]['order_item_status'] = $item['order_item_status'] ?? 0;
                $data['items'][$key]['status'] = $item['status'] ?? 1;
                $data['items'][$key]['is_cleaned'] = $item['is_cleaned'] ?? false;
                $data['items'][$key]['is_skinless'] = $item['is_skinless'] ?? false;

                // Calculate line_total if not set
                if (! isset($item['line_total']) || $item['line_total'] === null) {
                    $orderedWeight = (float) ($item['ordered_weight'] ?? 0);
                    $actualWeight = (float) ($item['actual_weight'] ?? 0);
                    $pricePerKg = (float) ($item['price_per_kg'] ?? 0);
                    $weightToUse = $actualWeight > 0 ? $actualWeight : $orderedWeight;
                    $lineSubtotal = $weightToUse * $pricePerKg;
                    $lineDiscount = (float) ($item['line_discount'] ?? 0);
                    $lineTax = (float) ($item['line_tax'] ?? 0);
                    $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;
                    $data['items'][$key]['line_total'] = $lineTotal;
                }
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->redirect(self::getResource()::getUrl('index'));
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('index');
    }
}
