<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Coupon;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ReferralPerformance extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.referral-performance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Trophy;

    public static function getNavigationLabel(): string
    {
        return 'Referral Performance';
    }

    public function getTitle(): string
    {
        return 'Referral Performance';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Coupon::query()
                    ->where('is_referral', true)
                    ->with('customer')
                    ->withCount([
                        'orders as total_orders_count' => fn (Builder $query) => $query->where('is_cancelled', false),
                        'orders as successful_orders_count' => fn (Builder $query) => $query
                            ->where('is_cancelled', false)
                            ->where('payment_status', 1)
                            ->whereNotNull('delivered_at'),
                    ])
                    ->withSum([
                        'orders as total_discount_given' => fn (Builder $query) => $query
                            ->where('is_cancelled', false)
                            ->where('payment_status', 1)
                            ->whereNotNull('delivered_at'),
                    ], 'discount_amount')
            )
            ->columns([
                TextColumn::make('customer.first_name')
                    ->label('Customer')
                    ->placeholder('—'),

                TextColumn::make('code')
                    ->label('Referral Code')
                    ->copyable(),

                TextColumn::make('total_orders_count')
                    ->label('Total Orders')
                    ->sortable(),

                TextColumn::make('successful_orders_count')
                    ->label('Successful Orders')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                TextColumn::make('conversion_rate')
                    ->label('Conversion Rate')
                    ->state(fn (Coupon $record): string => $record->total_orders_count > 0
                        ? number_format(($record->successful_orders_count / $record->total_orders_count) * 100, 1).'%'
                        : '—'
                    ),

                TextColumn::make('total_discount_given')
                    ->label('Total Discount Given')
                    ->formatStateUsing(fn (?string $state): string => '₹'.number_format((float) $state, 2))
                    ->sortable(),
            ])
            ->defaultSort('successful_orders_count', 'desc');
    }
}
