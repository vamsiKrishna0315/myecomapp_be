<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\CutType;
use App\Models\FlashBanner;
use App\Models\MetaTag;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCut;
use App\Models\WhyUs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('builds product image urls using the configured local media provider', function (): void {
    Config::set('media.default', 'local');
    Config::set('media.local.disk', 'public');
    Storage::fake('public');

    $product = Product::query()->create([
        'name' => 'Fresh Chicken',
        'price' => 299.00,
        'primary_image' => 'products/primary.jpg',
        'images' => ['products/gallery-1.jpg', 'products/gallery-2.jpg'],
    ])->refresh();

    expect($product->primary_image_url)
        ->toBe(Storage::disk('public')->url('products/primary.jpg'));

    expect($product->images_urls)
        ->toBe([
            Storage::disk('public')->url('products/gallery-1.jpg'),
            Storage::disk('public')->url('products/gallery-2.jpg'),
        ]);
});

it('builds product image urls using the configured supabase media provider', function (): void {
    Config::set('media.default', 'supabase');
    Config::set('media.supabase.url', 'https://project.supabase.co');
    Config::set('media.supabase.service_role_key', 'service-role-key');
    Config::set('media.supabase.public_bucket', 'yumeat-assets');

    $product = Product::query()->create([
        'name' => 'Fresh Mutton',
        'price' => 799.00,
        'primary_image' => 'products/mutton-primary.jpg',
        'images' => ['products/mutton-1.jpg', '/products/mutton-2.jpg'],
    ])->refresh();

    expect($product->primary_image_url)
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/products/mutton-primary.jpg');

    expect($product->images_urls)
        ->toBe([
            'https://project.supabase.co/storage/v1/object/public/yumeat-assets/products/mutton-1.jpg',
            'https://project.supabase.co/storage/v1/object/public/yumeat-assets/products/mutton-2.jpg',
        ]);
});

it('returns empty image urls when no images are stored', function (): void {
    Config::set('media.default', 'supabase');
    Config::set('media.supabase.url', 'https://project.supabase.co');
    Config::set('media.supabase.service_role_key', 'service-role-key');

    $product = Product::query()->create([
        'name' => 'Fresh Fish',
        'price' => 399.00,
    ])->refresh();

    expect($product->primary_image_url)->toBeNull()
        ->and($product->images_urls)->toBe([]);
});

it('builds category image urls using the configured media provider', function (): void {
    Config::set('media.default', 'supabase');
    Config::set('media.supabase.url', 'https://project.supabase.co');
    Config::set('media.supabase.service_role_key', 'service-role-key');
    Config::set('media.supabase.public_bucket', 'yumeat-assets');

    $category = Category::query()->create([
        'category_name' => 'Chicken',
        'category_type' => 'Meat',
        'category_image' => '/categories/chicken.jpg',
    ])->refresh();

    expect($category->category_image_url)
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/categories/chicken.jpg');
});

it('builds product cut image urls using the configured media provider', function (): void {
    Config::set('media.default', 'local');
    Config::set('media.local.disk', 'public');
    Storage::fake('public');

    $product = Product::query()->create([
        'sku' => 'PROD-000001',
        'name' => 'Test Product',
        'slug' => 'test-product',
        'price' => 100.00,
        'status' => 'draft',
    ]);

    $productCut = ProductCut::query()->create([
        'product_id' => $product->id,
        'cut_name' => 'Breast',
        'cut_code' => 'BRST',
        'price_per_kg' => 499,
        'image' => 'product-cuts/breast.jpg',
    ])->refresh();

    expect($productCut->image_url)
        ->toBe(Storage::disk('public')->url('product-cuts/breast.jpg'));
});

it('preserves absolute cut type icon urls and normalizes stored paths', function (): void {
    Config::set('media.default', 'supabase');
    Config::set('media.supabase.url', 'https://project.supabase.co');
    Config::set('media.supabase.service_role_key', 'service-role-key');
    Config::set('media.supabase.public_bucket', 'yumeat-assets');

    $cutType = CutType::query()->create([
        'name' => 'Premium Cut',
        'slug' => 'premium-cut',
        'icon' => '/cut-types/premium.svg',
    ])->refresh();

    expect($cutType->icon_url)
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/cut-types/premium.svg');

    $cutType->forceFill(['icon' => 'https://cdn.example.com/icons/premium.svg'])->save();
    $cutType->refresh();

    expect($cutType->icon_url)
        ->toBe('https://cdn.example.com/icons/premium.svg');
});

it('builds why us and flash banner image urls using the configured media provider', function (): void {
    Config::set('media.default', 'supabase');
    Config::set('media.supabase.url', 'https://project.supabase.co');
    Config::set('media.supabase.service_role_key', 'service-role-key');
    Config::set('media.supabase.public_bucket', 'yumeat-assets');

    $whyUs = WhyUs::query()->create([
        'title' => 'Farm Fresh',
        'image' => '/why-us/farm-fresh.jpg',
        'status' => true,
        'show_live' => true,
    ])->refresh();

    $flashBanner = FlashBanner::query()->create([
        'name' => 'Weekend Deal',
        'image' => 'flash-banners/weekend-deal.jpg',
        'status' => true,
        'is_live' => true,
    ])->refresh();

    expect($whyUs->image_url)
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/why-us/farm-fresh.jpg');

    expect($flashBanner->image_url)
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/flash-banners/weekend-deal.jpg');
});

it('serializes meta tag seo images with provider-aware urls', function (): void {
    Config::set('media.default', 'supabase');
    Config::set('media.supabase.url', 'https://project.supabase.co');
    Config::set('media.supabase.service_role_key', 'service-role-key');
    Config::set('media.supabase.public_bucket', 'yumeat-assets');

    $page = Page::query()->create([
        'title' => 'Home',
        'slug' => '/',
        'type' => 'page',
        'status' => true,
    ]);

    $metaTag = MetaTag::query()->create([
        'metaable_id' => $page->id,
        'metaable_type' => Page::class,
        'meta_title' => 'Home',
        'og_image' => '/seo/og-home.jpg',
        'status' => true,
    ])->refresh();

    expect($metaTag->og_image_url)
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/seo/og-home.jpg')
        ->and($metaTag->twitter_image_url)
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/seo/og-home.jpg')
        ->and($metaTag->seo['openGraph']['image'])
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/seo/og-home.jpg')
        ->and($metaTag->seo['twitter']['image'])
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/seo/og-home.jpg');

    $metaTag->forceFill([
        'twitter_image' => 'https://cdn.example.com/twitter-home.jpg',
    ])->save();

    $metaTag->refresh();

    expect($metaTag->twitter_image_url)
        ->toBe('https://cdn.example.com/twitter-home.jpg')
        ->and($metaTag->seo['twitter']['image'])
        ->toBe('https://cdn.example.com/twitter-home.jpg');
});

it('stores allowed units and grams per piece for products', function (): void {
    $product = new Product;
    $product->forceFill([
        'allowed_units' => ['gram', 'kg', 'piece'],
        'grams_per_piece' => 85.500,
    ]);

    expect($product->allowed_units)
        ->toBe(['gram', 'kg', 'piece'])
        ->and((string) $product->grams_per_piece)
        ->toBe('85.500');
});
