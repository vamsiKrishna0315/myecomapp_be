<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductsResource;
use Exception;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Log;

final class EditProducts extends EditRecord
{
    protected static string $resource = ProductsResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load existing cut types for the product
        $data['cuttype_ids'] = $this->record->cuttypes()->pluck('cut_types.id')->toArray();
        Log::info('EditProducts: mutateFormDataBeforeFill', [
            'product_id' => $this->record->id,
            'cuttype_ids' => $data['cuttype_ids'],
        ]);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Store cuttype_ids in session for afterUpdate
        if (isset($data['cuttype_ids'])) {
            session(['pending_cuttype_ids' => $data['cuttype_ids']]);
            Log::info('EditProducts: cuttype_ids captured', ['cuttype_ids' => $data['cuttype_ids']]);
            unset($data['cuttype_ids']); // Remove from product payload
        }
        Log::info('EditProducts: mutateFormDataBeforeSave', ['data' => $data]);

        return $data;
    }

    protected function afterSave(): void
    {
        $cuttypeIds = session('pending_cuttype_ids', []);
        Log::info('EditProducts: afterSave called', [
            'product_id' => $this->record?->id,
            'cuttype_ids' => $cuttypeIds,
        ]);
        try {
            if ($this->record && is_array($cuttypeIds)) {
                // Sync will automatically add new ones and remove old ones
                $this->record->cuttypes()->sync($cuttypeIds);
                Log::info('EditProducts: cuttypes synced', [
                    'product_id' => $this->record->id,
                    'cuttype_ids' => $cuttypeIds,
                ]);
            }
        } catch (Exception $e) {
            Log::error('EditProducts: Failed to sync cuttypes', [
                'error' => $e->getMessage(),
                'product_id' => $this->record?->id,
                'cuttype_ids' => $cuttypeIds,
            ]);
        }
        session()->forget('pending_cuttype_ids');
        $this->redirect(self::getResource()::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('index');
    }
}
