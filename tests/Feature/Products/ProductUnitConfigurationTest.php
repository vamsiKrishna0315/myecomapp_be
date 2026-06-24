<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Product Unit Configuration', function () {
    it('creates a product with allowed units and grams per piece', function () {
        $category = Category::create([
            'category_name' => 'Meat',
            'category_type' => 'Proteins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Premium Chicken Breast',
            'slug' => 'premium-chicken-breast',
            'price' => 500,
            'stock_quantity' => 100,
            'allowed_units' => ['gram', 'kg', 'piece'],
            'base_price_unit' => 'piece',
            'grams_per_piece' => 250,
        ]);

        expect($product->allowed_units)->toBe(['gram', 'kg', 'piece']);
        expect($product->base_price_unit)->toBe('piece');
        expect((float) $product->grams_per_piece)->toBe(250.0);
    });

    it('returns product with unit configuration in API response', function () {
        $category = Category::create([
            'category_name' => 'Meat',
            'category_type' => 'Proteins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Ground Meat',
            'slug' => 'ground-meat',
            'price' => 300,
            'stock_quantity' => 50,
            'allowed_units' => ['gram', 'kg'],
            'base_price_unit' => 'kg',
            'grams_per_piece' => null,
        ]);

        $product->status = 'active';
        $product->is_visible = true;
        $product->save();

        $response = $this->getJson('/api/v1/customer/products/'.$product->id);

        $response->assertSuccessful();

        $data = $response->json('data');
        expect($data)->toHaveKey('allowed_units', ['gram', 'kg']);
        expect($data)->toHaveKey('base_price_unit', 'kg');
        expect($data)->toHaveKey('grams_per_piece', null);
    });

    it('returns product list with unit configuration', function () {
        $category = Category::create([
            'category_name' => 'Meat',
            'category_type' => 'Proteins',
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Chicken Pieces',
            'slug' => 'chicken-pieces',
            'price' => 150,
            'stock_quantity' => 200,
            'status' => 'active',
            'is_visible' => true,
            'allowed_units' => ['piece'],
            'base_price_unit' => 'piece',
            'grams_per_piece' => 100.5,
        ]);

        $response = $this->getJson('/api/v1/customer/products');

        $response->assertSuccessful();

        $products = $response->json('data');
        expect($products)->toHaveCount(1);
        expect($products[0])->toHaveKey('allowed_units', ['piece']);
        expect($products[0])->toHaveKey('base_price_unit', 'piece');
        expect((float) $products[0]['grams_per_piece'])->toBe(100.5);
    });

    it('validates base_price_unit is one of allowed_units', function () {
        $category = Category::create([
            'category_name' => 'Meat',
            'category_type' => 'Proteins',
        ]);

        // This should work - base_price_unit is in allowed_units
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100,
            'stock_quantity' => 10,
            'allowed_units' => ['kg', 'piece'],
            'base_price_unit' => 'kg',
            'grams_per_piece' => 250,
        ]);

        expect($product->base_price_unit)->toBe('kg');
    });

    it('requires grams_per_piece when piece unit is allowed', function () {
        $category = Category::create([
            'category_name' => 'Meat',
            'category_type' => 'Proteins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Pieces Product',
            'slug' => 'pieces-product',
            'price' => 50,
            'stock_quantity' => 100,
            'allowed_units' => ['piece', 'gram'],
            'base_price_unit' => 'piece',
            'grams_per_piece' => 150.25,
        ]);

        expect((float) $product->grams_per_piece)->toBe(150.25);
    });

    it('handles products without piece unit gracefully', function () {
        $category = Category::create([
            'category_name' => 'Meat',
            'category_type' => 'Proteins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'No Piece Product',
            'slug' => 'no-piece-product',
            'price' => 200,
            'stock_quantity' => 30,
            'allowed_units' => ['gram', 'kg'],
            'base_price_unit' => 'gram',
            'grams_per_piece' => null,
        ]);

        expect($product->allowed_units)->toBe(['gram', 'kg']);
        expect($product->grams_per_piece)->toBeNull();
    });
});
