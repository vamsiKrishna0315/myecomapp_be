<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Traits\ChecksWidgetPermissions;
use App\Models\Driver;
use App\Models\Orders;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class DriverStatsOverviewWidget extends BaseWidget
{
    use ChecksWidgetPermissions;

    protected static ?int $sort = 9;

    public function getPollingInterval(): ?string
    {
        return '60s'; // Refresh every minute
    }

    protected function getStats(): array
    {
        $now = Carbon::now();

        // Total drivers
        $totalDrivers = Driver::count();

        // Active drivers
        $activeDrivers = Driver::where('status', 1)->count();

        // Available drivers
        $availableDrivers = Driver::where('status', 1)
            ->where('is_available', true)
            ->count();

        // Total deliveries today
        $todayDeliveries = Orders::whereNotNull('driver_id')
            ->whereDate('created_at', $now->today())
            ->count();

        // Total deliveries this month
        $currentMonthDeliveries = Orders::whereNotNull('driver_id')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Previous month deliveries
        $previousMonthDate = $now->copy()->subMonth();
        $previousMonthDeliveries = Orders::whereNotNull('driver_id')
            ->whereMonth('created_at', $previousMonthDate->month)
            ->whereYear('created_at', $previousMonthDate->year)
            ->count();

        // Calculate monthly change
        $monthlyChange = $previousMonthDeliveries > 0
            ? round((($currentMonthDeliveries - $previousMonthDeliveries) / $previousMonthDeliveries) * 100, 1)
            : 0;

        // Average deliveries per driver
        $avgDeliveriesPerDriver = $activeDrivers > 0
            ? round(Orders::whereNotNull('driver_id')->count() / $activeDrivers, 1)
            : 0;

        // Average driver rating
        $avgDriverRating = Driver::where('status', 1)
            ->whereNotNull('rating')
            ->avg('rating');
        $avgDriverRating = is_numeric($avgDriverRating)
            ? round((float) $avgDriverRating, 1)
            : 0;

        // Drivers with recent activity (last 7 days)
        $activeRecentDrivers = Driver::where('status', 1)
            ->whereHas('orders', function ($query) use ($now) {
                $query->where('created_at', '>=', $now->subDays(7));
            })
            ->count();

        return [
            Stat::make('Total Drivers', number_format($totalDrivers))
                ->description($activeDrivers.' active, '.$availableDrivers.' available')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),

            Stat::make('Deliveries This Month', number_format($currentMonthDeliveries))
                ->description($monthlyChange >= 0 ? "+{$monthlyChange}% from last month" : "{$monthlyChange}% from last month")
                ->descriptionIcon($monthlyChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyChange >= 0 ? 'success' : 'danger'),

            Stat::make('Today\'s Deliveries', number_format($todayDeliveries))
                ->description('Orders assigned to drivers today')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Driver Performance', $avgDriverRating.' ⭐')
                ->description('Average driver rating ('.$activeRecentDrivers.' active this week)')
                ->descriptionIcon('heroicon-m-star')
                ->color($avgDriverRating >= 4.0 ? 'success' : ($avgDriverRating >= 3.0 ? 'warning' : 'danger')),
        ];
    }
}
