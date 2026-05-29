<?php

namespace App\Filament\Resources\CutTypes;

use App\Filament\Resources\CutTypes\Pages\CreateCutType;
use App\Filament\Resources\CutTypes\Pages\EditCutType;
use App\Filament\Resources\CutTypes\Pages\ListCutTypes;
use App\Filament\Resources\CutTypes\Schemas\CutTypeForm;
use App\Filament\Resources\CutTypes\Tables\CutTypesTable;
use App\Models\CutType;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CutTypeResource extends Resource
{
    protected static ?string $model = CutType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Scissors;

    protected static string | UnitEnum | null $navigationGroup = 'Products Management';


    public static function form(Schema $schema): Schema
    {
        return CutTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CutTypesTable::configure($table);
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
            'index' => ListCutTypes::route('/'),
            'create' => CreateCutType::route('/create'),
            'edit' => EditCutType::route('/{record}/edit'),
        ];
    }
}
