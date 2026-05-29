<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Traits\ChecksWidgetPermissions;
use App\Models\Product;
use DB;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class TopSellingProductsWidget extends BaseWidget
{
    use ChecksWidgetPermissions;

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Top Selling Products';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select([
                        'products.*',
                        DB::raw('COUNT(order_items.id) as total_orders'),
                        DB::raw('COALESCE(SUM(order_items.actual_weight), SUM(order_items.ordered_weight)) as total_weight_sold'),
                        DB::raw('COALESCE(SUM(order_items.line_total), 0) as total_revenue'),
                        DB::raw('MAX(order_items.created_at) as last_ordered_date'),
                        DB::raw('AVG(order_items.line_total) as avg_order_value'),
                        DB::raw('categories.category_name as category_name'),
                    ])
                    ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                    ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                    ->where('products.status', 1)
                    ->where('products.is_visible', true)
                    ->groupBy('products.id', 'categories.category_name')
                    ->having('total_orders', '>', 0)
                    ->orderByDesc('total_orders')
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('primary_image')
                    ->label('Image')
                    ->circular()
                    ->size(50)
                    ->defaultImageUrl('/images/placeholder-product.png')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->description(fn ($record) => 'SKU: '.$record->sku),

                Tables\Columns\TextColumn::make('category_name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Orders')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->description(fn ($record) => 'Total orders placed'),

                Tables\Columns\TextColumn::make('total_weight_sold')
                    ->label('Weight Sold')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignCenter()
                    ->suffix(' kg')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('INR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('avg_order_value')
                    ->label('Avg Order')
                    ->money('INR')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '₹'.number_format((float) ($state ?? 0), 2)),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(function ($state, $record) {
                        if ($state <= ($record->low_stock_threshold ?? 10)) {
                            return 'danger';
                        }
                        if ($state <= (($record->low_stock_threshold ?? 10) * 2)) {
                            return 'warning';
                        }

                        return 'success';
                    }),

                Tables\Columns\TextColumn::make('last_ordered_date')
                    ->label('Last Ordered')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->description(function ($record) {
                        if ($record->last_ordered_date) {
                            return \Carbon\Carbon::parse($record->last_ordered_date)->diffForHumans();
                        }

                        return 'Never ordered';
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 1 ? 'Active' : 'Inactive')
                    ->color(fn ($state) => $state === 1 ? 'success' : 'danger')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'category_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('high_revenue')
                    ->label('High Revenue ($500+)')
                    ->query(fn ($query) => $query->having('total_revenue', '>=', 500)),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(function ($query) {
                        return $query->whereRaw('products.stock_quantity <= products.low_stock_threshold');
                    }),

                Tables\Filters\Filter::make('recent_orders')
                    ->label('Ordered Last 30 Days')
                    ->query(fn ($query) => $query->where('order_items.created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Action::make('view_orders')
                    ->label('View Orders')
                    ->icon('heroicon-o-shopping-bag')
                    ->url(fn ($record) => route('filament.admin.resources.order-items.index', ['tableFilters[product_id][value]' => $record->id]))
                    ->openUrlInNewTab(),

                Action::make('view_product')
                    ->label('Edit Product')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => route('filament.admin.resources.products.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated([10, 25, 50])
            ->defaultSort('total_orders', 'desc')
            ->poll('60s')
            ->emptyStateHeading('No product sales found')
            ->emptyStateDescription('When customers order products, sales analytics will appear here.')
            ->emptyStateIcon('heroicon-o-shopping-cart');
    }

    public function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50];
    }
}
