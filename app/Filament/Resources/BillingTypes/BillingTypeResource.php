<?php

namespace App\Filament\Resources\BillingTypes;

use App\Filament\Resources\BillingTypes\Pages\CreateBillingType;
use App\Filament\Resources\BillingTypes\Pages\EditBillingType;
use App\Filament\Resources\BillingTypes\Pages\ListBillingTypes;
use App\Filament\Resources\BillingTypes\Schemas\BillingTypeForm;
use App\Filament\Resources\BillingTypes\Tables\BillingTypesTable;
use App\Models\BillingType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BillingTypeResource extends Resource
{
    protected static ?string $model = BillingType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CreditCard;

    public static function form(Schema $schema): Schema
    {
        return BillingTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BillingTypesTable::configure($table);
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
            'index' => ListBillingTypes::route('/'),
            'create' => CreateBillingType::route('/create'),
            'edit' => EditBillingType::route('/{record}/edit'),
        ];
    }
}
