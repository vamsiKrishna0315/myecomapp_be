<?php

declare(strict_types=1);

namespace App\Filament\Resources\StoreContactInfos;

use App\Filament\Resources\StoreContactInfos\Pages\CreateStoreContactInfo;
use App\Filament\Resources\StoreContactInfos\Pages\EditStoreContactInfo;
use App\Filament\Resources\StoreContactInfos\Pages\ListStoreContactInfos;
use App\Filament\Resources\StoreContactInfos\Pages\ViewStoreContactInfo;
use App\Filament\Resources\StoreContactInfos\Schemas\StoreContactInfoForm;
use App\Filament\Resources\StoreContactInfos\Schemas\StoreContactInfoInfolist;
use App\Filament\Resources\StoreContactInfos\Tables\StoreContactInfosTable;
use App\Models\StoreContactInfo;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class StoreContactInfoResource extends Resource
{
    protected static ?string $model = StoreContactInfo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->can('ViewAny:StoreContactInfo') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return StoreContactInfoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StoreContactInfoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StoreContactInfosTable::configure($table);
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
            'index' => ListStoreContactInfos::route('/'),
            'create' => CreateStoreContactInfo::route('/create'),
            'view' => ViewStoreContactInfo::route('/{record}'),
            'edit' => EditStoreContactInfo::route('/{record}/edit'),
        ];
    }
}
