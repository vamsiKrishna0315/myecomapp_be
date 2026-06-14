<?php

declare(strict_types=1);

use App\Models\Product;
use App\Support\ProductSlugSupport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates unique slugs when a product name collides', function (): void {
    Product::query()->create([
        'name' => 'Chicken Breast Boneless',
        'price' => 329.00,
    ]);

    expect(ProductSlugSupport::generateUnique('Chicken Breast Boneless'))
        ->toBe('chicken-breast-boneless-2');
});

it('scores slug quality as pass or fail', function (): void {
    expect(ProductSlugSupport::evaluate('fresh'))
        ->toMatchArray([
            'status' => 'fail',
        ]);

    expect(ProductSlugSupport::evaluate('chicken-breast-boneless'))
        ->toMatchArray([
            'status' => 'pass',
        ]);
});

it('uses the hybrid canonical product url format', function (): void {
    $product = Product::query()->create([
        'name' => 'Fresh Mutton Curry Cut',
        'price' => 799.00,
    ]);

    expect($product->getSeoCanonicalUrl())
        ->toEndWith("/product/{$product->id}-{$product->slug}");
});
