<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerFavoriteItems;

use App\Filament\Resources\CustomerFavoriteItems\Pages\CreateCustomerFavoriteItem;
use App\Filament\Resources\CustomerFavoriteItems\Pages\EditCustomerFavoriteItem;
use App\Filament\Resources\CustomerFavoriteItems\Pages\ListCustomerFavoriteItems;
use App\Filament\Resources\CustomerFavoriteItems\Schemas\CustomerFavoriteItemForm;
use App\Filament\Resources\CustomerFavoriteItems\Tables\CustomerFavoriteItemsTable;
use App\Models\CustomerFavoriteItem;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class CustomerFavoriteItemResource extends Resource
{
    protected static ?string $model = CustomerFavoriteItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cube;

    protected static ?string $navigationLabel = 'Customer Favorites';

    protected static ?string $modelLabel = 'Customer Favorite';

    protected static ?string $pluralModelLabel = 'Customer Favorites';

    protected static string|UnitEnum|null $navigationGroup = 'Customer Management';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->can('ViewAny:CustomerFavoriteItem') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerFavoriteItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerFavoriteItemsTable::configure($table);
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
            'index' => ListCustomerFavoriteItems::route('/'),
            'create' => CreateCustomerFavoriteItem::route('/create'),
            'edit' => EditCustomerFavoriteItem::route('/{record}/edit'),
        ];
    }

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::where('status', 1)->count();
    // }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
