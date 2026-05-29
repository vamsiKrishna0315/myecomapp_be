<?php

declare(strict_types=1);

namespace App\Filament\Resources\VendorGamification;

use App\Filament\Resources\VendorGamification\Pages\ListVendorGamification;
use App\Filament\Resources\VendorGamification\Pages\ViewVendorGamification;
use App\Filament\Resources\VendorGamification\Pages\EditVendorGamification;
use App\Filament\Resources\VendorGamification\Schemas\VendorGamificationForm;
use App\Filament\Resources\VendorGamification\Tables\VendorGamificationTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class VendorGamificationResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static UnitEnum|string|null $navigationGroup = 'Vendor Management';

    protected static ?string $navigationLabel = 'Vendor Gamification';

    protected static ?string $modelLabel = 'Vendor Stats';

    protected static ?string $pluralModelLabel = 'Vendor Gamification';

    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_role', 'store_vendor')
            ->with(['store', 'badges', 'reputations']);
    }

    public static function form(Schema $schema): Schema
    {
        return VendorGamificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VendorGamificationTable::configure($table);
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
            'index' => ListVendorGamification::route('/'),
            'view' => ViewVendorGamification::route('/{record}'),
            'edit' => EditVendorGamification::route('/{record}/edit'),
        ];
    }
}
