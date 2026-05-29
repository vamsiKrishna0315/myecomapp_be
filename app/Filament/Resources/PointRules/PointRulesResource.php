<?php

declare(strict_types=1);

namespace App\Filament\Resources\PointRules;

use App\Filament\Resources\PointRules\Pages\CreatePointRules;
use App\Filament\Resources\PointRules\Pages\EditPointRules;
use App\Filament\Resources\PointRules\Pages\ListPointRules;
use App\Filament\Resources\PointRules\Schemas\PointRulesForm;
use App\Filament\Resources\PointRules\Tables\PointRulesTable;
use App\Models\GamificationPointRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PointRulesResource extends Resource
{
    protected static ?string $model = GamificationPointRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static UnitEnum|string|null $navigationGroup = 'Gamification';

    protected static ?string $navigationLabel = 'Point Rules';

    protected static ?string $modelLabel = 'Point Rule';

    protected static ?string $pluralModelLabel = 'Point Rules';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PointRulesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PointRulesTable::configure($table);
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
            'index' => ListPointRules::route('/'),
            'create' => CreatePointRules::route('/create'),
            'edit' => EditPointRules::route('/{record}/edit'),
        ];
    }
}
