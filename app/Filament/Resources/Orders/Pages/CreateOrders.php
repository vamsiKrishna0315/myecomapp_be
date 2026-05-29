<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrdersResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrders extends CreateRecord
{
    protected static string $resource = OrdersResource::class;

    // Store vendor data temporarily
    protected array $storeVendorData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store vendor assignment data (will be handled after order creation)
        // Remove these from the order data as they don't belong to orders table
        if (isset($data['store_id'])) {
            $pending = [
                'store_id' => $data['store_id'],
                'store_vendor_id' => $data['store_vendor_id'] ?? null,
                'is_eligible' => $data['is_eligible'] ?? null,
            ];

            // Persist to session so it's available after Livewire lifecycle
            session(['pending_store_vendor_assignment' => $pending]);
            \Log::info('Store vendor data captured to session', $pending);

            // Remove from order payload so Orders table doesn't receive these fields
            unset($data['store_id'], $data['store_vendor_id'], $data['is_eligible']);
        }

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
                if (!isset($item['line_total']) || $item['line_total'] === null) {
                    $orderedWeight = (float) ($item['ordered_weight'] ?? 0);
                    $actualWeight = (float) ($item['actual_weight'] ?? 0);
                    $pricePerKg = (float) ($item['price_per_kg'] ?? 0);
                    $weightToUse = $actualWeight > 0 ? $actualWeight : $orderedWeight;
                    $lineSubtotal = $weightToUse * $pricePerKg;
                    $lineDiscount = (float) ($item['line_discount'] ?? 0);
                    $lineTax = (float) ($item['line_tax'] ?? 0);
                    $lineTotal = $lineSubtotal - $lineDiscount + $lineTax; // Correct formula
                    $data['items'][$key]['line_total'] = $lineTotal;
                }
                
                \Log::info('Order item data before create', $data['items'][$key]);
            }
        }
        
        \Log::info('Full order data before create', $data);
        return $data;
    }

    protected function afterCreate(): void
    {
        // Insert store vendor assignment after order is created
        $pending = session('pending_store_vendor_assignment');
        if (!empty($pending) && isset($pending['store_id']) && isset($pending['store_vendor_id'])) {
            try {
                \App\Models\StoreVendorOrders::create([
                    'order_id' => $this->record->id,
                    'customer_id' => $this->record->customer_id,
                    'store_id' => $pending['store_id'],
                    'store_vendor_id' => $pending['store_vendor_id'],
                    'is_eligible' => $pending['is_eligible'] ?? true,
                    'status' => 1, // Active status
                ]);

                \Log::info('StoreVendorOrders created successfully (afterCreate)', [
                    'order_id' => $this->record->id,
                    'store_id' => $pending['store_id'],
                    'store_vendor_id' => $pending['store_vendor_id'],
                ]);

                // Clear session
                session()->forget('pending_store_vendor_assignment');
            } catch (\Exception $e) {
                \Log::error('Failed to create StoreVendorOrders (afterCreate)', [
                    'error' => $e->getMessage(),
                    'order_id' => $this->record->id,
                    'data' => $pending,
                ]);
            }
        } else {
            \Log::warning('No store vendor data to insert (afterCreate)', [
                'order_id' => $this->record->id,
                'pending' => $pending,
            ]);
        }
    }

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }

    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
