<?php

namespace App\Filament\Resources\OrderReviews;

use App\Filament\Resources\OrderReviews\Pages\CreateOrderReview;
use App\Filament\Resources\OrderReviews\Pages\EditOrderReview;
use App\Filament\Resources\OrderReviews\Pages\ListOrderReviews;
use App\Filament\Resources\OrderReviews\Schemas\OrderReviewForm;
use App\Filament\Resources\OrderReviews\Tables\OrderReviewsTable;
use App\Models\OrderReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OrderReviewResource extends Resource
{
    protected static ?string $model = OrderReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Star;

    protected static UnitEnum|string|null $navigationGroup = 'Order Management';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return OrderReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderReviewsTable::configure($table);
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
            'index' => ListOrderReviews::route('/'),
            'create' => CreateOrderReview::route('/create'),
            'edit' => EditOrderReview::route('/{record}/edit'),
        ];
    }
}
