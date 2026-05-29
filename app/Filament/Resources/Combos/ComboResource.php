<?php

declare(strict_types=1);

namespace App\Filament\Resources\Combos;

use App\Filament\Resources\Combos\Pages\CreateCombo;
use App\Filament\Resources\Combos\Pages\EditCombo;
use App\Filament\Resources\Combos\Pages\ListCombos;
use App\Filament\Resources\Combos\Relations\ComboItemRelationManager;
use App\Filament\Resources\Combos\Schemas\ComboForm;
use App\Filament\Resources\Combos\Tables\CombosTable;
use App\Models\Combo;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class ComboResource extends Resource
{
    protected static ?string $model = Combo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'Product Management';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->can('ViewAny:Combo') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return ComboForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CombosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ComboItemRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCombos::route('/'),
            'create' => CreateCombo::route('/create'),
            'edit' => EditCombo::route('/{record}/edit'),
        ];
    }
}
