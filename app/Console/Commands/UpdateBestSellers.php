<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\OrderItems;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class UpdateBestSellers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-best-sellers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update best seller products based on last 3 months orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting best sellers update...');

        // Reset all products is_best_seller to 0
        Product::query()->update(['is_best_seller' => 0]);
        $this->info('Reset all products is_best_seller to 0');

        // Get date range for previous 3 months
        $startDate = Carbon::now()->subMonths(3)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $this->info("Analyzing orders from {$startDate->toDateString()} to {$endDate->toDateString()}");

        // Get top 3 products from last week's orders
        $topProducts = OrderItems::select(
            'product_id',
            DB::raw('COUNT(DISTINCT order_id) as total_orders'),
            DB::raw('SUM(ordered_weight) as total_weight'),
            DB::raw('SUM(line_total) as total_revenue')
        )
            ->whereHas('order', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 1) // Active orders only
                    ->where('is_cancelled', false); // Not cancelled
            })
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue') // Order by revenue
            ->limit(3)
            ->get();

        if ($topProducts->isEmpty()) {
            $this->warn('No orders found in the last 3 months.');

            return 0;
        }

        // Update top 3 products
        foreach ($topProducts as $index => $item) {
            Product::where('id', $item->product_id)->update(['is_best_seller' => 1]);

            $product = Product::find($item->product_id);
            $this->info(sprintf(
                '#%d: %s - Orders: %d, Weight: %.2f kg, Revenue: ₹%.2f',
                $index + 1,
                $product->name ?? 'Unknown',
                $item->total_orders,
                $item->total_weight,
                $item->total_revenue
            ));
        }

        $this->info('✅ Best sellers updated successfully!');

        return 0;
    }
}
