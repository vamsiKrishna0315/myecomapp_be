<?php

declare(strict_types=1);

use App\Http\Requests\Api\V1\Order\OrderItemRequest;
use App\Http\Requests\Api\V1\Order\StoreOrderRequest;

it('uses only kg, gram, and piece in order item unit validation rules', function (): void {
    $storeRules = (new StoreOrderRequest)->rules();
    $itemRules = (new OrderItemRequest)->rules();

    expect($storeRules['items.*.weight_unit'])
        ->toBe('nullable|string|in:kg,gram,piece')
        ->and($itemRules['weight_unit'])
        ->toBe('nullable|string|in:kg,gram,piece');
});
