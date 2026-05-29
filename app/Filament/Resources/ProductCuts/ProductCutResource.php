<?php

namespace App\Filament\Resources\ProductCuts;

use App\Filament\Resources\ProductCuts\Pages\CreateProductCut;
use App\Filament\Resources\ProductCuts\Pages\EditProductCut;
use App\Filament\Resources\ProductCuts\Pages\ListProductCuts;
use App\Filament\Resources\ProductCuts\Schemas\ProductCutForm;
use App\Filament\Resources\ProductCuts\Tables\ProductCutsTable;
use App\Models\ProductCut;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductCutResource extends Resource
{
    protected static ?string $model = ProductCut::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Slash;

    protected static string | UnitEnum | null $navigationGroup = 'Products Management';

    public static function form(Schema $schema): Schema
    {
        return ProductCutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductCutsTable::configure($table);
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
            'index' => ListProductCuts::route('/'),
            'create' => CreateProductCut::route('/create'),
            'edit' => EditProductCut::route('/{record}/edit'),
        ];
    }
}
