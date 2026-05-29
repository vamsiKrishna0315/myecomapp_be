<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\CouponType;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Coupon Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                BadgeColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ((int) $state) {
                        CouponType::Percentage->value => 'Percentage',
                        CouponType::FixedAmount->value => 'Fixed Amount',
                        default => 'Unknown',
                    })
                    ->colors([
                        'success' => CouponType::Percentage->value,
                        'primary' => CouponType::FixedAmount->value,
                    ])
                    ->sortable(),

                TextColumn::make('value')
                    ->label('Discount Value')
                    ->formatStateUsing(function ($record): string {
                        if ($record->type == CouponType::Percentage->value) {
                            return $record->value . '%';
                        }
                        return '₹' . number_format($record->value, 2);
                    })
                    ->sortable(),

                TextColumn::make('min_order_amount')
                    ->label('Min Order')
                    ->formatStateUsing(fn (?string $state): string => $state ? '₹' . number_format($state, 2) : 'No minimum')
                    ->sortable(),

                TextColumn::make('usage_limit')
                    ->label('Usage Limit')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('used_count')
                    ->label('Used')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('valid_from')
                    ->label('Valid From')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('valid_until')
                    ->label('Valid Until')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color(function ($record): string {
                        $validUntil = \Carbon\Carbon::parse($record->valid_until);
                        $now = now();
                        
                        if ($validUntil->isPast()) {
                            return 'danger'; // Expired
                        } elseif ($validUntil->diffInDays($now) <= 7) {
                            return 'warning'; // Expiring soon
                        }
                        return 'success'; // Valid
                    }),

                IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->placeholder('All Statuses'),

                SelectFilter::make('type')
                    ->label('Discount Type')
                    ->options([
                        CouponType::Percentage->value => 'Percentage',
                        CouponType::FixedAmount->value => 'Fixed Amount',
                    ])
                    ->placeholder('All Types'),

                Filter::make('validity')
                    ->label('Validity Status')
                    ->form([
                        \Filament\Forms\Components\Select::make('validity_status')
                            ->options([
                                'active' => 'Currently Valid',
                                'expired' => 'Expired',
                                'upcoming' => 'Not Yet Started',
                                'expiring_soon' => 'Expiring Soon (7 days)',
                            ])
                            ->placeholder('All Validity Statuses'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['validity_status'],
                            function (Builder $query, $status) {
                                $now = now();
                                return match ($status) {
                                    'active' => $query->where('valid_from', '<=', $now)
                                                   ->where('valid_until', '>=', $now),
                                    'expired' => $query->where('valid_until', '<', $now),
                                    'upcoming' => $query->where('valid_from', '>', $now),
                                    'expiring_soon' => $query->where('valid_until', '>=', $now)
                                                           ->where('valid_until', '<=', $now->addDays(7)),
                                    default => $query,
                                };
                            }
                        );
                    }),

                Filter::make('usage_status')
                    ->label('Usage Status')
                    ->form([
                        \Filament\Forms\Components\Select::make('usage_type')
                            ->options([
                                'unused' => 'Never Used',
                                'used' => 'Used',
                                'limit_reached' => 'Usage Limit Reached',
                            ])
                            ->placeholder('All Usage Types'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['usage_type'],
                            function (Builder $query, $type) {
                                return match ($type) {
                                    'unused' => $query->where('used_count', 0),
                                    'used' => $query->where('used_count', '>', 0),
                                    'limit_reached' => $query->whereRaw('used_count >= usage_limit'),
                                    default => $query,
                                };
                            }
                        );
                    }),

                Filter::make('created_from')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Created From')
                            ->placeholder('Select start date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            );
                    }),

                Filter::make('created_until')
                    ->form([
                        DatePicker::make('created_until')
                            ->label('Created Until')
                            ->placeholder('Select end date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->searchable()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }
}
