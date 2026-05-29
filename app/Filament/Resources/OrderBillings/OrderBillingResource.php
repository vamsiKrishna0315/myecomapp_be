<?php

namespace App\Filament\Resources\OrderBillings;

use App\Filament\Resources\OrderBillings\Pages\CreateOrderBilling;
use App\Filament\Resources\OrderBillings\Pages\EditOrderBilling;
use App\Filament\Resources\OrderBillings\Pages\ListOrderBillings;
use App\Filament\Resources\OrderBillings\Schemas\OrderBillingForm;
use App\Filament\Resources\OrderBillings\Tables\OrderBillingsTable;
use App\Models\OrderBilling;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OrderBillingResource extends Resource
{
    protected static ?string $model = OrderBilling::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCurrencyDollar;

    protected static UnitEnum|string|null $navigationGroup = 'Order Management';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return OrderBillingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderBillingsTable::configure($table);
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
            'index' => ListOrderBillings::route('/'),
            'create' => CreateOrderBilling::route('/create'),
            'edit' => EditOrderBilling::route('/{record}/edit'),
        ];
    }
}
