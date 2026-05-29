<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo 'Orders count: '.App\Models\Orders::count().PHP_EOL;
echo 'OrderItems count: '.App\Models\OrderItems::count().PHP_EOL;
echo 'Products count: '.App\Models\Product::count().PHP_EOL;
echo 'Categories count: '.App\Models\Category::count().PHP_EOL;

echo "\nSample OrderItems:\n";
$items = App\Models\OrderItems::with('order')->limit(3)->get();
foreach ($items as $item) {
    echo "Product ID: {$item->product_id}, Cut ID: {$item->cut_id}, Order ID: {$item->order_id}\n";
    if ($item->order) {
        echo "  Order Status: {$item->order->status}, Cancelled: {$item->order->is_cancelled}\n";
    }
}
