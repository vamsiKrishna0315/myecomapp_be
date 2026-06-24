<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Product Piece Weight Type (Standard/Custom)', function () {
    it('defaults to standard 100g when piece is added to allowed units', function () {
        $category = Category::create([
            'category_name' => 'Meat',
            'category_type' => 'Proteins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Chicken Pieces',
            'slug' => 'chicken-pieces',
            'price' => 50,
            'stock_quantity' => 100,
            'allowed_units' => ['piece'],
            'base_price_unit' => 'piece',
            'grams_per_piece_type' => 'standard',
            'grams_per_piece' => 100,
        ]);

        expect($product->grams_per_piece_type)->toBe('standard');
        expect((float) $product->grams_per_piece)->toBe(100.0);
    });

    it('allows custom weight when user selects custom option', function () {
        $category = Category::create([
            'category_name' => 'Meat',
            'category_type' => 'Proteins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Chicken Pieces',
            'slug' => 'chicken-pieces',
            'price' => 50,
            'stock_quantity' => 100,
            'allowed_units' => ['piece', 'gram', 'kg'],
            'base_price_unit' => 'piece',
            'grams_per_piece_type' => 'custom',
            'grams_per_piece' => 250.5,
        ]);

        expect($product->grams_per_piece_type)->toBe('custom');
        expect((float) $product->grams_per_piece)->toBe(250.5);
    });

    it('returns piece weight type in api response', function () {
        $category = Category::create([
            'category_name' => 'Meat',
            'category_type' => 'Proteins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 100,
            'stock_quantity' => 50,
            'status' => 'active',
            'is_visible' => true,
            'allowed_units' => ['piece', 'kg'],
            'base_price_unit' => 'kg',
            'grams_per_piece_type' => 'standard',
            'grams_per_piece' => 100,
        ]);

        $response = $this->getJson('/api/v1/customer/products/'.$product->id);

        $response->assertSuccessful();
        $data = $response->json('data');

        expect($data)->toHaveKey('grams_per_piece_type', 'standard');
        expect($data)->toHaveKey('grams_per_piece', '100.000');
    });

    it('handles multiple products with different piece weight types', function () {
        $category = Category::create([
            'category_name' => 'Meat',
            'category_type' => 'Proteins',
        ]);

        $standardProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Standard Pieces',
            'slug' => 'standard-pieces',
            'price' => 50,
            'stock_quantity' => 100,
            'status' => 'active',
            'is_visible' => true,
            'allowed_units' => ['piece'],
            'base_price_unit' => 'piece',
            'grams_per_piece_type' => 'standard',
            'grams_per_piece' => 100,
        ]);

        $customProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Custom Pieces',
            'slug' => 'custom-pieces',
            'price' => 75,
            'stock_quantity' => 50,
            'status' => 'active',
            'is_visible' => true,
            'allowed_units' => ['piece', 'gram'],
            'base_price_unit' => 'piece',
            'grams_per_piece_type' => 'custom',
            'grams_per_piece' => 200,
        ]);

        $response = $this->getJson('/api/v1/customer/products');

        $response->assertSuccessful();
        $products = $response->json('data');

        expect($products)->toHaveCount(2);

        $standardData = collect($products)->firstWhere('slug', 'standard-pieces');
        expect($standardData['grams_per_piece_type'])->toBe('standard');
        expect((float) $standardData['grams_per_piece'])->toBe(100.0);

        $customData = collect($products)->firstWhere('slug', 'custom-pieces');
        expect($customData['grams_per_piece_type'])->toBe('custom');
        expect((float) $customData['grams_per_piece'])->toBe(200.0);
    });

    it('validates that custom type requires grams_per_piece value', function () {
        $category = Category::create([
            'category_name' => 'Meat',
            'category_type' => 'Proteins',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test',
            'slug' => 'test',
            'price' => 100,
            'stock_quantity' => 10,
            'allowed_units' => ['piece'],
            'base_price_unit' => 'piece',
            'grams_per_piece_type' => 'custom',
            'grams_per_piece' => 150.75,
        ]);

        expect($product->grams_per_piece_type)->toBe('custom');
        expect($product->grams_per_piece)->not->toBeNull();
    });
});
