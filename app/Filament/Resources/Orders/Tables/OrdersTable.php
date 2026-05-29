<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\OrderStatuses;
use App\Models\Driver;
use App\Models\BillingType;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        // Badge color will be derived dynamically from the related OrderStatuses record
        // (the Orders model has the `currentStatus` relation).

        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('primary')
                    ->weight('bold'),

                TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable()
                    ->description(fn ($record) => $record->customer?->email)
                    ->limit(30),

                BadgeColumn::make('currentStatus.name')
                    ->label('Order Status')
                    ->color(fn ($record) => $record->currentStatus?->color ?? 'gray')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->formatStateUsing(fn (int $state): string => match($state) {
                        PaymentStatus::Pending->value => 'Pending',
                        PaymentStatus::Paid->value => 'Paid',
                        PaymentStatus::Failed->value => 'Failed',
                        PaymentStatus::Refunded->value => 'Refunded',
                        PaymentStatus::PartialRefund->value => 'Partial Refund',
                        default => 'Unknown'
                    })
                    ->colors([
                        'danger' => [PaymentStatus::Failed->value, PaymentStatus::Refunded->value],
                        'warning' => [PaymentStatus::Pending->value, PaymentStatus::PartialRefund->value],
                        'success' => PaymentStatus::Paid->value,
                    ])
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('INR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('delivery_date')
                    ->label('Delivery Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn ($record) => $record->delivery_time_slot),

                TextColumn::make('driver.name')
                    ->label('Driver')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not Assigned')
                    ->description(fn ($record) => $record->driver?->mobile),

                TextColumn::make('deliveryAddress.city')
                    ->label('Delivery Area')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->deliveryAddress?->state),

                TextColumn::make('billingType.name')
                    ->label('Payment Method')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_cancelled')
                    ->label('Cancelled')
                    ->boolean()
                    ->alignCenter()
                    ->tooltip('Order cancelled status'),

                TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->money('INR')
                    ->sortable()
                    ->placeholder('No Discount')
                    ->description(fn ($record) => $record->coupon_code ? "Code: {$record->coupon_code}" : null),

                BadgeColumn::make('status')
                    ->label('Active')
                    ->formatStateUsing(fn (int $state): string => $state ? 'Active' : 'Inactive')
                    ->colors([
                        'success' => 1,
                        'danger' => 0,
                    ]),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('confirmed_at')
                    ->label('Confirmed')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Not Confirmed')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivered_at')
                    ->label('Delivered')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Not Delivered')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('current_status_id')
                    ->label('Order Status')
                    ->relationship('currentStatus', 'name')
                    ->preload()
                    ->multiple(),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        PaymentStatus::Pending->value => 'Pending',
                        PaymentStatus::Paid->value => 'Paid',
                        PaymentStatus::Failed->value => 'Failed',
                        PaymentStatus::Refunded->value => 'Refunded',
                        PaymentStatus::PartialRefund->value => 'Partial Refund',
                    ])
                    ->multiple(),

                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload(),

                SelectFilter::make('driver_id')
                    ->label('Driver')
                    ->relationship('driver', 'name')
                    ->preload()
                    ->multiple(),

                SelectFilter::make('billing_type_id')
                    ->label('Payment Method')
                    ->relationship('billingType', 'name')
                    ->preload()
                    ->multiple(),

                Filter::make('delivery_area')
                    ->form([
                        Select::make('city')
                            ->label('Delivery City')
                            ->options(function () {
                                return \App\Models\Address::whereHas('deliveryOrders')
                                    ->distinct()
                                    ->pluck('city', 'city')
                                    ->filter();
                            })
                            ->searchable(),
                        
                        Select::make('state')
                            ->label('Delivery State')
                            ->options(function () {
                                return \App\Models\Address::whereHas('deliveryOrders')
                                    ->distinct()
                                    ->pluck('state', 'state')
                                    ->filter();
                            })
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['city'],
                                fn (Builder $query, $city): Builder => $query->whereHas(
                                    'deliveryAddress',
                                    fn (Builder $query) => $query->where('city', $city)
                                )
                            )
                            ->when(
                                $data['state'],
                                fn (Builder $query, $state): Builder => $query->whereHas(
                                    'deliveryAddress',
                                    fn (Builder $query) => $query->where('state', $state)
                                )
                            );
                    }),

                Filter::make('delivery_date_range')
                    ->form([
                        DatePicker::make('from')
                            ->label('Delivery From'),
                        DatePicker::make('until')
                            ->label('Delivery Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('delivery_date', '<=', $date),
                            );
                    }),

                Filter::make('order_amount_range')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min_amount')
                            ->label('Min Amount')
                            ->numeric()
                            ->prefix('₹'),
                        \Filament\Forms\Components\TextInput::make('max_amount')
                            ->label('Max Amount')
                            ->numeric()
                            ->prefix('₹'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '>=', $amount),
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '<=', $amount),
                            );
                    }),

                TernaryFilter::make('is_cancelled')
                    ->label('Cancelled Orders')
                    ->placeholder('All orders')
                    ->trueLabel('Cancelled only')
                    ->falseLabel('Active only'),

                TernaryFilter::make('has_discount')
                    ->label('Has Discount')
                    ->placeholder('All orders')
                    ->trueLabel('With discount')
                    ->falseLabel('No discount')
                    ->queries(
                        true: fn (Builder $query) => $query->where('discount_amount', '>', 0),
                        false: fn (Builder $query) => $query->where('discount_amount', '<=', 0),
                    ),

                SelectFilter::make('status')
                    ->label('Record Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->tooltip('View order details'),
                EditAction::make()
                    ->tooltip('Edit order'),
                // Override single-record delete to soft-like update instead of actual delete
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $deletedStatus = OrderStatuses::where('code', 'deleted')->first();

                        $record->update([
                            'status' => 0,
                            'current_status_id' => $deletedStatus?->id,
                            'current_status_code' => $deletedStatus?->code ?? 'deleted',
                        ]);
                    })
                    ->color('danger')
                    ->tooltip('Mark order as deleted'),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('New Order')
                    ->icon('heroicon-o-plus'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Bulk "delete" will perform the same update behavior as the single delete
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $deletedStatus = OrderStatuses::where('code', 'deleted')->first();

                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 0,
                                    'current_status_id' => $deletedStatus?->id,
                                    'current_status_code' => $deletedStatus?->code ?? 'deleted',
                                ]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->persistSortInSession();
    }
}
