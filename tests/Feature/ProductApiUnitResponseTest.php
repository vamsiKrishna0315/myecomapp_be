<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns allowed units and base price unit in product json response', function () {
    $product = new Product();
    $product->forceFill([
        'allowed_units' => ['gram', 'kg', 'piece'],
        'base_price_unit' => 'gram',
        'grams_per_piece' => 100,
    ]);

    $json = $product->toJson();

    expect($json)->toContain('"allowed_units":["gram","kg","piece"]');
    expect($json)->toContain('"base_price_unit":"gram"');
    // grams_per_piece is decimal:3 so it serializes as string
    expect($json)->toContain('"grams_per_piece":"100.000"');
});

it('includes unit fields in api response array', function () {
    $product = new Product();
    $product->forceFill([
        'id' => 1,
        'name' => 'Premium Chicken',
        'allowed_units' => ['kg'],
        'base_price_unit' => 'kg',
        'grams_per_piece' => null,
    ]);

    $array = $product->toArray();

    expect($array)->toHaveKey('allowed_units', ['kg']);
    expect($array)->toHaveKey('base_price_unit', 'kg');
    expect($array)->toHaveKey('grams_per_piece', null);
});
