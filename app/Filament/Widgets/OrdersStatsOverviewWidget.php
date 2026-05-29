<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Traits\ChecksWidgetPermissions;
use App\Models\Orders;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class OrdersStatsOverviewWidget extends BaseWidget
{
    use ChecksWidgetPermissions;

    protected static ?int $sort = 1;

    public function getPollingInterval(): ?string
    {
        return '30s'; // Refresh every 30 seconds
    }

    protected function getStats(): array
    {
        $now = Carbon::now();

        // Current month orders
        $currentMonth = Orders::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Previous month orders
        $previousMonthDate = $now->copy()->subMonth();
        $previousMonth = Orders::whereMonth('created_at', $previousMonthDate->month)
            ->whereYear('created_at', $previousMonthDate->year)
            ->count();

        // Calculate percentage change
        $monthlyChange = $previousMonth > 0
            ? round((($currentMonth - $previousMonth) / $previousMonth) * 100, 1)
            : 0;

        // Current week orders
        $currentWeekStart = $now->copy()->startOfWeek();
        $currentWeekEnd = $now->copy()->endOfWeek();
        $currentWeek = Orders::whereBetween('created_at', [
            $currentWeekStart,
            $currentWeekEnd,
        ])->count();

        // Previous week orders
        $previousWeekStart = $now->copy()->subWeek()->startOfWeek();
        $previousWeekEnd = $now->copy()->subWeek()->endOfWeek();
        $previousWeek = Orders::whereBetween('created_at', [
            $previousWeekStart,
            $previousWeekEnd,
        ])->count();

        // Calculate weekly percentage change
        $weeklyChange = $previousWeek > 0
            ? round((($currentWeek - $previousWeek) / $previousWeek) * 100, 1)
            : 0;

        // Total orders
        $totalOrders = Orders::count();

        // Today's orders
        $todayOrders = Orders::whereDate('created_at', $now->today())->count();

        return [
            Stat::make('Total Orders', number_format($totalOrders))
                ->description('All time orders')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('This Month', number_format($currentMonth))
                ->description($monthlyChange >= 0 ? "+{$monthlyChange}% from last month" : "{$monthlyChange}% from last month")
                ->descriptionIcon($monthlyChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyChange >= 0 ? 'success' : 'danger'),

            Stat::make('This Week', number_format($currentWeek))
                ->description($weeklyChange >= 0 ? "+{$weeklyChange}% from last week" : "{$weeklyChange}% from last week")
                ->descriptionIcon($weeklyChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($weeklyChange >= 0 ? 'success' : 'danger'),

            Stat::make('Today', number_format($todayOrders))
                ->description('Orders placed today')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
        ];
    }
}
