<?php

namespace App\Filament\Resources\ProductGrades;

use App\Filament\Resources\ProductGrades\Pages\CreateProductGrade;
use App\Filament\Resources\ProductGrades\Pages\EditProductGrade;
use App\Filament\Resources\ProductGrades\Pages\ListProductGrades;
use App\Filament\Resources\ProductGrades\Schemas\ProductGradeForm;
use App\Filament\Resources\ProductGrades\Tables\ProductGradesTable;
use App\Models\ProductGrade;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductGradeResource extends Resource
{
    protected static ?string $model = ProductGrade::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckBadge;

    protected static string | UnitEnum | null $navigationGroup = 'Products Management';

    public static function form(Schema $schema): Schema
    {
        return ProductGradeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductGradesTable::configure($table);
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
            'index' => ListProductGrades::route('/'),
            'create' => CreateProductGrade::route('/create'),
            'edit' => EditProductGrade::route('/{record}/edit'),
        ];
    }
}
