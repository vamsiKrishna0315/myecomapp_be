<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Traits\ChecksWidgetPermissions;
use App\Models\Driver;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

final class DriverAnalyticsWidget extends BaseWidget
{
    use ChecksWidgetPermissions;

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Driver Analytics';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Driver::query()
                    ->select([
                        'drivers.*',
                        DB::raw('COUNT(orders.id) as total_deliveries'),
                        DB::raw('COALESCE(SUM(orders.total_amount), 0) as total_delivery_value'),
                        DB::raw('COUNT(CASE WHEN orders.status = 1 THEN 1 END) as completed_deliveries'),
                        DB::raw('COUNT(CASE WHEN orders.created_at >= CURDATE() - INTERVAL 30 DAY THEN 1 END) as recent_deliveries'),
                        DB::raw('MAX(orders.delivered_at) as last_delivery_date'),
                        DB::raw('AVG(orders.total_amount) as avg_order_value'),
                    ])
                    ->leftJoin('orders', 'drivers.id', '=', 'orders.driver_id')
                    ->where('drivers.status', 1)
                    ->groupBy('drivers.id')
                    ->orderByDesc('total_deliveries')
                    ->limit(20)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Driver Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->email),

                Tables\Columns\TextColumn::make('mobile')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Vehicle')
                    ->badge()
                    ->color('primary')
                    ->description(fn ($record) => $record->vehicle_number),

                Tables\Columns\TextColumn::make('total_deliveries')
                    ->label('Total Deliveries')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('completed_deliveries')
                    ->label('Completed')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->description(function ($record) {
                        if ($record->total_deliveries > 0) {
                            $rate = round(($record->completed_deliveries / $record->total_deliveries) * 100, 1);

                            return $rate.'% completion rate';
                        }

                        return 'No deliveries';
                    }),

                Tables\Columns\TextColumn::make('recent_deliveries')
                    ->label('Last 30 Days')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('total_delivery_value')
                    ->label('Total Value')
                    ->money('INR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('avg_order_value')
                    ->label('Avg Order')
                    ->money('INR')
                    ->sortable()
                    ->formatStateUsing(function ($state): string {
                        $value = is_numeric($state) ? (float) $state : 0.0;

                        return '₹'.number_format($value, 2);
                    }),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->badge()
                    ->color(function ($state) {
                        if ($state >= 4.5) {
                            return 'success';
                        }
                        if ($state >= 4.0) {
                            return 'warning';
                        }
                        if ($state >= 3.0) {
                            return 'danger';
                        }

                        return 'gray';
                    })
                    ->formatStateUsing(fn ($state) => $state ? $state.' ⭐' : 'No rating'),

                Tables\Columns\TextColumn::make('last_delivery_date')
                    ->label('Last Delivery')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->description(function ($record) {
                        if ($record->last_delivery_date) {
                            return Carbon::parse($record->last_delivery_date)->diffForHumans();
                        }

                        return 'No deliveries yet';
                    }),

                Tables\Columns\TextColumn::make('is_available')
                    ->label('Available')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Available' : 'Unavailable')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('experience_years')
                    ->label('Experience')
                    ->numeric()
                    ->sortable()
                    ->suffix(' years')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_type')
                    ->options([
                        'bike' => 'Bike',
                        'car' => 'Car',
                        'van' => 'Van',
                        'truck' => 'Truck',
                    ]),

                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Availability')
                    ->trueLabel('Available')
                    ->falseLabel('Unavailable'),

                Tables\Filters\Filter::make('high_performers')
                    ->label('High Performers (20+ Deliveries)')
                    ->query(fn ($query) => $query->having('total_deliveries', '>=', 20)),

                Tables\Filters\Filter::make('top_rated')
                    ->label('Top Rated (4.0+ Stars)')
                    ->query(fn ($query) => $query->where('drivers.rating', '>=', 4.0)),

                Tables\Filters\Filter::make('active_drivers')
                    ->label('Active Last 30 Days')
                    ->query(fn ($query) => $query->having('recent_deliveries', '>', 0)),
            ])
            ->actions([
                Action::make('view_deliveries')
                    ->label('View Orders')
                    ->icon('heroicon-o-truck')
                    ->url(fn ($record) => route('filament.admin.resources.orders.index', ['tableFilters[driver_id][value]' => $record->id]))
                    ->openUrlInNewTab(),

                Action::make('view_profile')
                    ->label('Profile')
                    ->icon('heroicon-o-user')
                    ->url(fn ($record) => route('filament.admin.resources.drivers.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated([10, 25, 50])
            ->defaultSort('total_deliveries', 'desc')
            ->poll('60s')
            ->emptyStateHeading('No driver deliveries found')
            ->emptyStateDescription('When drivers complete deliveries, their analytics will appear here.')
            ->emptyStateIcon('heroicon-o-truck');
    }

    public function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50];
    }
}
