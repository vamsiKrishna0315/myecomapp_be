<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Traits\ChecksWidgetPermissions;
use App\Models\Customer;
use App\Models\Orders;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class CustomerStatsOverviewWidget extends BaseWidget
{
    use ChecksWidgetPermissions;

    protected static ?int $sort = 3;

    public function getPollingInterval(): ?string
    {
        return '60s'; // Refresh every minute
    }

    protected function getStats(): array
    {
        $now = Carbon::now();

        // Total customers
        $totalCustomers = Customer::count();

        // Active customers
        $activeCustomers = Customer::active()->count();

        // New customers this month
        $currentMonthCustomers = Customer::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // New customers last month
        $previousMonthDate = $now->copy()->subMonth();
        $previousMonthCustomers = Customer::whereMonth('created_at', $previousMonthDate->month)
            ->whereYear('created_at', $previousMonthDate->year)
            ->count();

        // Calculate monthly growth
        $monthlyGrowth = $previousMonthCustomers > 0
            ? round((($currentMonthCustomers - $previousMonthCustomers) / $previousMonthCustomers) * 100, 1)
            : 0;

        // Customers with orders
        $customersWithOrders = Customer::whereHas('orders')->count();

        // Customer conversion rate
        $conversionRate = $totalCustomers > 0
            ? round(($customersWithOrders / $totalCustomers) * 100, 1)
            : 0;

        // New customers today
        $todayCustomers = Customer::whereDate('created_at', $now->today())->count();

        // Average orders per customer
        $avgOrdersPerCustomer = $customersWithOrders > 0
            ? round(Orders::count() / $customersWithOrders, 1)
            : 0;

        return [
            Stat::make('Total Customers', number_format($totalCustomers))
                ->description($activeCustomers.' active customers')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('New This Month', number_format($currentMonthCustomers))
                ->description($monthlyGrowth >= 0 ? "+{$monthlyGrowth}% from last month" : "{$monthlyGrowth}% from last month")
                ->descriptionIcon($monthlyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('Customer Conversion', $conversionRate.'%')
                ->description($customersWithOrders.' customers with orders')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($conversionRate >= 50 ? 'success' : ($conversionRate >= 25 ? 'warning' : 'danger')),

            Stat::make('Avg Orders/Customer', $avgOrdersPerCustomer)
                ->description('Orders per active customer')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
}
