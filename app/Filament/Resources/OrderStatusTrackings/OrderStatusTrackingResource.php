<?php

namespace App\Filament\Resources\OrderStatusTrackings;

use App\Filament\Resources\OrderStatusTrackings\Pages\CreateOrderStatusTracking;
use App\Filament\Resources\OrderStatusTrackings\Pages\EditOrderStatusTracking;
use App\Filament\Resources\OrderStatusTrackings\Pages\ListOrderStatusTrackings;
use App\Filament\Resources\OrderStatusTrackings\Schemas\OrderStatusTrackingForm;
use App\Filament\Resources\OrderStatusTrackings\Tables\OrderStatusTrackingsTable;
use App\Models\OrderStatusTracking;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class OrderStatusTrackingResource extends Resource
{
    protected static ?string $model = OrderStatusTracking::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Order Status Tracking';
    
    protected static ?string $modelLabel = 'Order Status Tracking';
    
    protected static ?string $pluralModelLabel = 'Order Status Tracking';

    protected static UnitEnum|string|null $navigationGroup = 'Order Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return OrderStatusTrackingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderStatusTrackingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderStatusTrackings::route('/'),
            'create' => CreateOrderStatusTracking::route('/create'),
            'edit' => EditOrderStatusTracking::route('/{record}/edit'),
            'timeline' => \App\Filament\Resources\OrderStatusTrackings\Pages\OrderTimeline::route('/{record}/timeline'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 1)->count();
    }
}
