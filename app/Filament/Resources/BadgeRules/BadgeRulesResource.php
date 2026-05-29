<?php

declare(strict_types=1);

namespace App\Filament\Resources\BadgeRules;

use App\Filament\Resources\BadgeRules\Pages\CreateBadgeRules;
use App\Filament\Resources\BadgeRules\Pages\EditBadgeRules;
use App\Filament\Resources\BadgeRules\Pages\ListBadgeRules;
use App\Filament\Resources\BadgeRules\Schemas\BadgeRulesForm;
use App\Filament\Resources\BadgeRules\Tables\BadgeRulesTable;
use App\Models\GamificationBadgeRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BadgeRulesResource extends Resource
{
    protected static ?string $model = GamificationBadgeRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static UnitEnum|string|null $navigationGroup = 'Gamification';

    protected static ?string $navigationLabel = 'Badge Rules';

    protected static ?string $modelLabel = 'Badge Rule';

    protected static ?string $pluralModelLabel = 'Badge Rules';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return BadgeRulesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BadgeRulesTable::configure($table);
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
            'index' => ListBadgeRules::route('/'),
            'create' => CreateBadgeRules::route('/create'),
            'edit' => EditBadgeRules::route('/{record}/edit'),
        ];
    }
}
