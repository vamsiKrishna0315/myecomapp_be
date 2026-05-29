<?php

declare(strict_types=1);

namespace App\Filament\Resources\VendorGamification\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class VendorGamificationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Vendor Name')
                    ->weight('bold'),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('Email')
                    ->copyable(),

                TextColumn::make('store.name')
                    ->searchable()
                    ->sortable()
                    ->label('Store')
                    ->placeholder('No Store Assigned'),

                BadgeColumn::make('reputation')
                    ->sortable()
                    ->label('Reputation')
                    ->default(0)
                    ->colors([
                        'success' => fn ($state) => $state >= 1000,
                        'warning' => fn ($state) => $state >= 500 && $state < 1000,
                        'info' => fn ($state) => $state >= 100 && $state < 500,
                        'gray' => fn ($state) => $state < 100,
                    ]),

                BadgeColumn::make('badges_count')
                    ->counts('badges')
                    ->label('Badges')
                    ->color('primary'),

                TextColumn::make('orders_count')
                    ->label('Total Orders')
                    ->getStateUsing(function ($record) {
                        return \App\Models\StoreVendorOrders::where('store_vendor_id', $record->id)->count();
                    })
                    ->sortable(query: function (Builder $query, string $direction) {
                        return $query
                            ->withCount(['storeVendorOrders'])
                            ->orderBy('store_vendor_orders_count', $direction);
                    })
                    ->badge()
                    ->color('gray'),

                BadgeColumn::make('rank')
                    ->label('Rank')
                    ->getStateUsing(function ($record) {
                        $rank = User::where('user_role', 'store_vendor')
                            ->where('reputation', '>', $record->reputation ?? 0)
                            ->count() + 1;
                        return "#$rank";
                    })
                    ->color('warning'),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('high_performers')
                    ->query(fn (Builder $query) => $query->where('reputation', '>=', 500))
                    ->label('High Performers (500+ points)')
                    ->toggle(),

                Filter::make('with_badges')
                    ->query(fn (Builder $query) => $query->has('badges'))
                    ->label('Has Badges')
                    ->toggle(),

                SelectFilter::make('store_id')
                    ->label('Store')
                    ->relationship('store', 'name')
                    ->preload()
                    ->multiple(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->tooltip('View vendor details'),
                EditAction::make()
                    ->tooltip('Edit reputation'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('reputation', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->persistFiltersInSession()
            ->persistSortInSession();
    }
}
