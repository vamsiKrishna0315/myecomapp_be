<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Traits\ChecksWidgetPermissions;
use App\Models\Customer;
use DB;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class TopCustomersWidget extends BaseWidget
{
    use ChecksWidgetPermissions;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Top Customers';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Customer::query()
                    ->select([
                        'customers.*',
                        DB::raw('COUNT(orders.id) as total_orders'),
                        DB::raw('COALESCE(SUM(orders.total_amount), 0) as total_spent'),
                        DB::raw('MAX(orders.created_at) as last_order_date'),
                    ])
                    ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
                    ->groupBy('customers.id')
                    ->having('total_orders', '>', 0)
                    ->orderByDesc('total_spent')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Customer')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        return $record->full_name;
                    })
                    ->description(fn ($record) => $record->email),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('Phone')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_orders')
                    ->label('Total Orders')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Spent')
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

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 1 ? 'Active' : 'Inactive')
                    ->color(fn ($state) => $state === 1 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Member Since')
                    ->date('M j, Y')
                    ->sortable()
                    ->toggleable()
                    ->description(function ($record) {
                        return \Carbon\Carbon::parse($record->created_at)->diffForHumans();
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1),
            ])
            ->actions([
                Action::make('view_orders')
                    ->label('View Orders')
                    ->icon('heroicon-o-shopping-bag')
                    ->url(fn ($record) => route('filament.admin.resources.orders.index', ['tableFilters[customer_id][value]' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->paginated([5, 10, 25])
            ->defaultSort('total_spent', 'desc')
            ->poll('60s')
            ->emptyStateHeading('No customer orders found')
            ->emptyStateDescription('When customers place orders, they will appear here.')
            ->emptyStateIcon('heroicon-o-users');
    }

    public function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25];
    }
}
