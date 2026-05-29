<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking order statuses:\n";
$orders = App\Models\Orders::limit(5)->get(['id', 'status', 'is_cancelled', 'created_at']);
foreach ($orders as $order) {
    echo "Order {$order->id}: status={$order->status}, is_cancelled=";
    var_dump($order->is_cancelled);
}

echo "\n\nChecking order items with valid orders:\n";
$items = App\Models\OrderItems::whereHas('order', function ($q) {
    $q->where('status', 1);
})->limit(5)->get(['id', 'product_id', 'cut_id', 'order_id', 'line_total', 'actual_weight']);

foreach ($items as $item) {
    echo "Item {$item->id}: product={$item->product_id}, cut={$item->cut_id}, total={$item->line_total}, weight={$item->actual_weight}\n";
}
