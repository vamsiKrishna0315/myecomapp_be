<?php

namespace App\Filament\Resources\OrderStatusTrackings\Pages;

use App\Filament\Resources\OrderStatusTrackings\OrderStatusTrackingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderStatusTracking extends EditRecord
{
    protected static string $resource = OrderStatusTrackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            \Filament\Actions\Action::make('track_package')
                ->label('Track Package')
                ->icon('heroicon-o-clock')
                ->url(fn ($record) => url("/admin/order-status-trackings/{$record->id}/timeline"))
                ->openUrlInNewTab(),
        ];
    }
}
