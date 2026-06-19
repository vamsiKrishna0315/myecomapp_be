<?php

declare(strict_types=1);

use App\Models\FlashBanner;
use Illuminate\Support\Facades\Config;

it('resolves the image url via the media service', function (): void {
    Config::set('media.default', 'local');
    Config::set('media.local.disk', 'public');

    $banner = FlashBanner::factory()->create([
        'image' => 'flash-banners/sample.jpg',
    ]);

    expect($banner->image_url)->toContain('flash-banners/sample.jpg');
});

it('returns null image url when no image is set', function (): void {
    $banner = new FlashBanner(['image' => null]);

    expect($banner->image_url)->toBeNull();
});

it('builds a supabase image url when the supabase provider is active', function (): void {
    Config::set('media.default', 'supabase');
    Config::set('media.supabase.url', 'https://project.supabase.co');
    Config::set('media.supabase.service_role_key', 'service-role-key');
    Config::set('media.supabase.public_bucket', 'yumeat-assets');

    $banner = FlashBanner::factory()->create([
        'image' => 'flash-banners/hero.jpg',
    ]);

    expect($banner->image_url)
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/flash-banners/hero.jpg');
});
