<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Traits\ChecksWidgetPermissions;
use App\Models\Orders;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class TotalOrdersWidget extends ChartWidget
{
    use ChecksWidgetPermissions;

    public ?string $filter = 'monthly';

    protected ?string $heading = 'Total Orders';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public function getDescription(): ?string
    {
        if ($this->filter === 'weekly') {
            return 'Total orders over the last 12 weeks';
        }

        return 'Total orders over the last 12 months';
    }

    public function getPollingInterval(): ?string
    {
        return '30s'; // Refresh every 30 seconds
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

        $orders = Orders::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $period = $date->format('Y-m');
            $label = $date->format('M Y');

            $labels[] = $label;
            $data[] = $orders->get($period)?->count ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getWeeklyData(): array
    {
        $now = Carbon::now();
        $startDate = $now->copy()->subWeeks(11)->startOfWeek();

        $orders = Orders::select(
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
        $ordersLookup = [];
        foreach ($orders as $order) {
            $key = $order->year_num.'-'.$order->week_num;
            $ordersLookup[$key] = $order->count;
        }

        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $year = $weekStart->year;
            $week = $weekStart->week;
            $lookupKey = $year.'-'.$week;

            $label = $weekStart->format('M d').' - '.$weekEnd->format('M d');

            $labels[] = $label;
            $data[] = $ordersLookup[$lookupKey] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
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
