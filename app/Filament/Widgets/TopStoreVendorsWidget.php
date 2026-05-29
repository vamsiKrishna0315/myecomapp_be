<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Traits\ChecksWidgetPermissions;
use App\Models\User;
use DB;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class TopStoreVendorsWidget extends BaseWidget
{
    use ChecksWidgetPermissions;

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Top Store Vendors';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->select([
                        'users.*',
                        DB::raw('COUNT(store_vendor_orders.id) as total_orders'),
                        DB::raw('COALESCE(SUM(orders.total_amount), 0) as total_revenue'),
                        DB::raw('MAX(store_vendor_orders.created_at) as last_order_date'),
                        DB::raw('COUNT(CASE WHEN orders.status = 1 THEN 1 END) as completed_orders'),
                        DB::raw('stores.name as store_name'),
                    ])
                    ->leftJoin('store_vendor_orders', 'users.id', '=', 'store_vendor_orders.store_vendor_id')
                    ->leftJoin('orders', 'store_vendor_orders.order_id', '=', 'orders.id')
                    ->leftJoin('stores', 'users.store_id', '=', 'stores.id')
                    ->where('users.user_role', 'store_vendor')
                    ->where('users.status', 1)
                    ->groupBy('users.id', 'stores.name')
                    ->having('total_orders', '>', 0)
                    ->orderByDesc('total_orders')
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Vendor Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->email),

                Tables\Columns\TextColumn::make('store_name')
                    ->label('Store')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('user_mobile_no')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Total Orders')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('completed_orders')
                    ->label('Completed')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->description(function ($record) {
                        if ($record->total_orders > 0) {
                            $rate = round(($record->completed_orders / $record->total_orders) * 100, 1);

                            return $rate.'% completion rate';
                        }

                        return 'No orders';
                    }),

                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Revenue')
                    ->money('INR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('last_order_date')
                    ->label('Last Order')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->description(function ($record) {
                        if ($record->last_order_date) {
                            return \Carbon\Carbon::parse($record->last_order_date)->diffForHumans();
                        }

                        return null;
                    }),

                Tables\Columns\TextColumn::make('joining_date')
                    ->label('Joined')
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable()
                    ->description(function ($record) {
                        if ($record->joining_date) {
                            return \Carbon\Carbon::parse($record->joining_date)->diffForHumans();
                        }

                        return null;
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 1 ? 'Active' : 'Inactive')
                    ->color(fn ($state) => $state === 1 ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('store_id')
                    ->label('Store')
                    ->relationship('store', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('high_performers')
                    ->label('High Performers (10+ Orders)')
                    ->query(fn ($query) => $query->having('total_orders', '>=', 10)),

                Tables\Filters\Filter::make('recent_activity')
                    ->label('Active Last 30 Days')
                    ->query(fn ($query) => $query->where('store_vendor_orders.created_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Action::make('view_orders')
                    ->label('View Orders')
                    ->icon('heroicon-o-shopping-bag')
                    ->url(fn ($record) => route('filament.admin.resources.orders.index', ['tableFilters[store_vendor_id][value]' => $record->id]))
                    ->openUrlInNewTab(),

                Action::make('view_profile')
                    ->label('Profile')
                    ->icon('heroicon-o-user')
                    ->url(fn ($record) => route('filament.admin.resources.users.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated([10, 25, 50])
            ->defaultSort('total_orders', 'desc')
            ->poll('60s')
            ->emptyStateHeading('No store vendor orders found')
            ->emptyStateDescription('When store vendors process orders, they will appear here.')
            ->emptyStateIcon('heroicon-o-building-storefront');
    }

    public function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50];
    }
}
