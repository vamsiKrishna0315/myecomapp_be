<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Traits\ChecksWidgetPermissions;
use App\Models\Customer;
use Carbon\Carbon;
use DB;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class NewCustomersWidget extends BaseWidget
{
    use ChecksWidgetPermissions;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recently Joined Customers';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Customer::query()
                    ->select([
                        'customers.*',
                        DB::raw('COUNT(orders.id) as order_count'),
                        DB::raw('COALESCE(SUM(orders.total_amount), 0) as total_spent'),
                    ])
                    ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
                    ->where('customers.created_at', '>=', Carbon::now()->subDays(30))
                    ->groupBy('customers.id')
                    ->orderByDesc('customers.created_at')
                    ->limit(15)
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->description(function ($record) {
                        return Carbon::parse($record->created_at)->diffForHumans();
                    })
                    ->badge()
                    ->color(function ($record) {
                        $days = Carbon::parse($record->created_at)->diffInDays(now());
                        if ($days <= 1) {
                            return 'success';
                        }
                        if ($days <= 7) {
                            return 'warning';
                        }

                        return 'gray';
                    }),

                Tables\Columns\TextColumn::make('order_count')
                    ->label('Orders')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(function ($state) {
                        if ($state === 0) {
                            return 'gray';
                        }
                        if ($state >= 3) {
                            return 'success';
                        }

                        return 'primary';
                    })
                    ->formatStateUsing(fn ($state) => $state ?: 'No orders yet'),

                Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Spent')
                    ->money('INR')
                    ->sortable()
                    ->color(function ($state) {
                        if ($state === 0) {
                            return 'gray';
                        }
                        if ($state >= 100) {
                            return 'success';
                        }

                        return 'primary';
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 1 ? 'Active' : 'Inactive')
                    ->color(fn ($state) => $state === 1 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('dob')
                    ->label('Age')
                    ->formatStateUsing(function ($state) {
                        if (! $state) {
                            return 'N/A';
                        }

                        return Carbon::parse($state)->age.' years';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('period')
                    ->options([
                        '1' => 'Last 24 hours',
                        '7' => 'Last 7 days',
                        '30' => 'Last 30 days',
                    ])
                    ->default('30')
                    ->query(function ($query, $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        $days = (int) $data['value'];

                        return $query->where('customers.created_at', '>=', Carbon::now()->subDays($days));
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),

                Tables\Filters\TernaryFilter::make('has_orders')
                    ->label('Has Orders')
                    ->trueLabel('With Orders')
                    ->falseLabel('Without Orders')
                    ->queries(
                        true: fn ($query) => $query->having('order_count', '>', 0),
                        false: fn ($query) => $query->having('order_count', '=', 0),
                    ),
            ])
            ->actions([
                Action::make('view_customer')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.customers.edit', $record))
                    ->openUrlInNewTab(),

                Action::make('view_orders')
                    ->label('Orders')
                    ->icon('heroicon-o-shopping-bag')
                    ->url(fn ($record) => route('filament.admin.resources.orders.index', ['tableFilters[customer_id][value]' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->order_count > 0),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_active')
                        ->label('Mark as Active')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['status' => 1]))
                        ->color('success')
                        ->requiresConfirmation(),
                ]),
            ])
            ->paginated([10, 25, 50])
            ->defaultSort('created_at', 'desc')
            ->poll('60s')
            ->emptyStateHeading('No new customers')
            ->emptyStateDescription('New customer registrations will appear here.')
            ->emptyStateIcon('heroicon-o-user-plus');
    }

    public function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50];
    }
}
