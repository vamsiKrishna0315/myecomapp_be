<?php

namespace App\Filament\Resources\OrderCancellations;

use App\Filament\Resources\OrderCancellations\Pages\CreateOrderCancellation;
use App\Filament\Resources\OrderCancellations\Pages\EditOrderCancellation;
use App\Filament\Resources\OrderCancellations\Pages\ListOrderCancellations;
use App\Filament\Resources\OrderCancellations\Schemas\OrderCancellationForm;
use App\Filament\Resources\OrderCancellations\Tables\OrderCancellationsTable;
use App\Models\OrderCancellation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OrderCancellationResource extends Resource
{
    protected static ?string $model = OrderCancellation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::XCircle;

    protected static UnitEnum|string|null $navigationGroup = 'Order Management';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return OrderCancellationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderCancellationsTable::configure($table);
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
            'index' => ListOrderCancellations::route('/'),
            'create' => CreateOrderCancellation::route('/create'),
            'edit' => EditOrderCancellation::route('/{record}/edit'),
        ];
    }
}
