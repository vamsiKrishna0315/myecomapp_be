<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductsResource;
use Exception;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

final class CreateProducts extends CreateRecord
{
    protected static string $resource = ProductsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store cuttype_ids in session for afterCreate
        if (isset($data['cuttype_ids'])) {
            session(['pending_cuttype_ids' => $data['cuttype_ids']]);
            unset($data['cuttype_ids']); // Remove from product payload
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $cuttypeIds = session('pending_cuttype_ids', []);
        try {
            if ($this->record && is_array($cuttypeIds) && count($cuttypeIds)) {
                $this->record->cuttypes()->sync($cuttypeIds);
            }

        } catch (Exception $e) {
            Log::error('CreateProducts: Failed to sync cuttypes', [
                'error' => $e->getMessage(),
                'product_id' => $this->record?->id,
                'cuttype_ids' => $cuttypeIds,
            ]);
        }
        session()->forget('pending_cuttype_ids');
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
