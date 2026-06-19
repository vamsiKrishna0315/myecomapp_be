<?php

declare(strict_types=1);

use App\Models\Driver;
use App\Models\FlashBanner;
use App\Models\MetaTag;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\Storage;

it('migrates legacy media paths for additional models and keeps external urls intact', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    $flashBanner = FlashBanner::query()->create([
        'name' => 'Weekend Offer',
        'image' => '/storage/legacy/flash-banner.jpg',
    ]);

    $store = Store::query()->create([
        'name' => 'YumEat',
        'phone' => '9000000000',
        'pincode' => '500001',
        'logo' => '/storage/legacy/store-logo.png',
        'favicon' => 'legacy/store-favicon.png',
    ]);

    $driver = Driver::query()->create([
        'name' => 'Driver One',
        'mobile' => '9000000001',
        'email' => 'driver-one@example.com',
        'password' => bcrypt('password'),
        'vehicle_type' => 1,
        'vehicle_number' => 'AP01AA0001',
        'license_number' => 'LIC001',
        'profile_image' => '/storage/legacy/profile.jpg',
        'driver_license_image' => 'legacy/license.jpg',
        'vehicle_registration_image' => 'legacy/registration.jpg',
        'insurance_image' => 'legacy/insurance.jpg',
    ]);

    $product = Product::query()->create([
        'sku' => 'PROD-900001',
        'name' => 'Meta Product',
        'slug' => 'meta-product',
        'price' => 100,
        'status' => 'draft',
    ]);

    $metaTag = MetaTag::query()->create([
        'metaable_id' => $product->id,
        'metaable_type' => Product::class,
        'og_image' => '/storage/legacy/og-image.jpg',
        'twitter_image' => 'https://cdn.example.com/twitter.jpg',
    ]);

    Storage::disk('local')->put('legacy/flash-banner.jpg', 'flash');
    Storage::disk('local')->put('legacy/store-logo.png', 'logo');
    Storage::disk('local')->put('legacy/store-favicon.png', 'favicon');
    Storage::disk('local')->put('legacy/profile.jpg', 'profile');
    Storage::disk('local')->put('legacy/license.jpg', 'license');
    Storage::disk('local')->put('legacy/registration.jpg', 'registration');
    Storage::disk('local')->put('legacy/insurance.jpg', 'insurance');
    Storage::disk('local')->put('legacy/og-image.jpg', 'og');

    $this->artisan('media:migrate-to-public')->assertSuccessful();

    expect($flashBanner->refresh()->image)->toBe('flash-banners/flash-banner.jpg');
    expect($store->refresh()->logo)->toBe('stores/store-logo.png')
        ->and($store->refresh()->favicon)->toBe('stores/store-favicon.png');
    expect($driver->refresh()->profile_image)->toBe('drivers/profile.jpg')
        ->and($driver->refresh()->driver_license_image)->toBe('licenses/license.jpg')
        ->and($driver->refresh()->vehicle_registration_image)->toBe('registrations/registration.jpg')
        ->and($driver->refresh()->insurance_image)->toBe('insurance/insurance.jpg');
    expect($metaTag->refresh()->og_image)->toBe('seo/og-image.jpg')
        ->and($metaTag->refresh()->twitter_image)->toBe('https://cdn.example.com/twitter.jpg');

    Storage::disk('public')->assertExists('flash-banners/flash-banner.jpg');
    Storage::disk('public')->assertExists('stores/store-logo.png');
    Storage::disk('public')->assertExists('stores/store-favicon.png');
    Storage::disk('public')->assertExists('drivers/profile.jpg');
    Storage::disk('public')->assertExists('licenses/license.jpg');
    Storage::disk('public')->assertExists('registrations/registration.jpg');
    Storage::disk('public')->assertExists('insurance/insurance.jpg');
    Storage::disk('public')->assertExists('seo/og-image.jpg');
});

it('does not persist database updates during dry run', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    $flashBanner = FlashBanner::query()->create([
        'name' => 'Dry Run Banner',
        'image' => 'legacy/dry-run.jpg',
    ]);

    Storage::disk('local')->put('legacy/dry-run.jpg', 'dry-run');

    $this->artisan('media:migrate-to-public', ['--dry' => true])->assertSuccessful();

    expect($flashBanner->refresh()->image)->toBe('legacy/dry-run.jpg');
    Storage::disk('public')->assertMissing('flash-banners/dry-run.jpg');
});
