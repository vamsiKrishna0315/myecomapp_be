<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Traits\ChecksWidgetPermissions;
use App\Models\Customer;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class CustomerGrowthChartWidget extends ChartWidget
{
    use ChecksWidgetPermissions;

    public ?string $filter = 'monthly';

    protected ?string $heading = 'Customer Growth';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public function getDescription(): ?string
    {
        if ($this->filter === 'weekly') {
            return 'Customer registration trends over the last 12 weeks';
        }

        return 'Customer registration trends over the last 12 months';
    }

    public function getPollingInterval(): ?string
    {
        return '60s'; // Refresh every minute
    }

    protected function getFilters(): ?array
    {
        return [
            'monthly' => 'Monthly',
            'weekly' => 'Weekly',
        ];
    }

    protected function getData(): array
    {
        $filter = $this->filter;

        if ($filter === 'weekly') {
            return $this->getWeeklyData();
        }

        return $this->getMonthlyData();
    }

    protected function getMonthlyData(): array
    {
        $now = Carbon::now();
        $startDate = $now->copy()->subMonths(11)->startOfMonth();

        $customers = Customer::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $labels = [];
        $newCustomers = [];
        $totalCustomers = [];
        $runningTotal = Customer::where('created_at', '<', $startDate)->count();

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $period = $date->format('Y-m');
            $label = $date->format('M Y');

            $monthlyCount = $customers->get($period)?->count ?? 0;
            $runningTotal += $monthlyCount;

            $labels[] = $label;
            $newCustomers[] = $monthlyCount;
            $totalCustomers[] = $runningTotal;
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Customers',
                    'data' => $newCustomers,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'type' => 'bar',
                ],
                [
                    'label' => 'Total Customers',
                    'data' => $totalCustomers,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                    'type' => 'line',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getWeeklyData(): array
    {
        $now = Carbon::now();
        $startDate = $now->copy()->subWeeks(11)->startOfWeek();

        $customers = Customer::select(
            DB::raw('WEEK(created_at, 1) as week_num'),
            DB::raw('YEAR(created_at) as year_num'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy(['year_num', 'week_num'])
            ->orderBy('year_num')
            ->orderBy('week_num')
            ->get();

        // Create a lookup array
        $customersLookup = [];
        foreach ($customers as $customer) {
            $key = $customer->year_num.'-'.$customer->week_num;
            $customersLookup[$key] = $customer->count;
        }

        $labels = [];
        $newCustomers = [];
        $totalCustomers = [];
        $runningTotal = Customer::where('created_at', '<', $startDate)->count();

        for ($i = 11; $i >= 0; $i--) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $year = $weekStart->year;
            $week = $weekStart->week;
            $lookupKey = $year.'-'.$week;

            $weeklyCount = $customersLookup[$lookupKey] ?? 0;
            $runningTotal += $weeklyCount;

            $label = $weekStart->format('M d').' - '.$weekEnd->format('M d');

            $labels[] = $label;
            $newCustomers[] = $weeklyCount;
            $totalCustomers[] = $runningTotal;
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Customers',
                    'data' => $newCustomers,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'type' => 'bar',
                ],
                [
                    'label' => 'Total Customers',
                    'data' => $totalCustomers,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                    'type' => 'line',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
                'x' => [
                    'display' => true,
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
            'maintainAspectRatio' => false,
            'responsive' => true,
        ];
    }
}
