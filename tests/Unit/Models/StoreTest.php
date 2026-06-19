<?php

declare(strict_types=1);

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('builds store logo and favicon urls using the configured local media provider', function (): void {
    Config::set('media.default', 'local');
    Config::set('media.local.disk', 'public');
    Storage::fake('public');

    $store = Store::query()->create([
        'name' => 'YumEat',
        'phone' => '9999999999',
        'pincode' => '600001',
        'logo' => 'stores/logo.png',
        'favicon' => 'stores/favicon.png',
    ])->refresh();

    expect($store->logo_url)
        ->toBe(Storage::disk('public')->url('stores/logo.png'));

    expect($store->favicon_url)
        ->toBe(Storage::disk('public')->url('stores/favicon.png'));
});

it('builds store logo and favicon urls using the configured supabase media provider', function (): void {
    Config::set('media.default', 'supabase');
    Config::set('media.supabase.url', 'https://project.supabase.co');
    Config::set('media.supabase.service_role_key', 'service-role-key');
    Config::set('media.supabase.public_bucket', 'yumeat-assets');

    $store = Store::query()->create([
        'name' => 'YumEat',
        'phone' => '9999999999',
        'pincode' => '600001',
        'logo' => 'stores/logo.png',
        'favicon' => '/stores/favicon.png',
    ])->refresh();

    expect($store->logo_url)
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/stores/logo.png');

    expect($store->favicon_url)
        ->toBe('https://project.supabase.co/storage/v1/object/public/yumeat-assets/stores/favicon.png');
});

it('returns null urls when store logo and favicon are empty', function (): void {
    Config::set('media.default', 'supabase');
    Config::set('media.supabase.url', 'https://project.supabase.co');
    Config::set('media.supabase.service_role_key', 'service-role-key');

    $store = Store::query()->create([
        'name' => 'YumEat',
        'phone' => '9999999999',
        'pincode' => '600001',
    ])->refresh();

    expect($store->logo_url)->toBeNull()
        ->and($store->favicon_url)->toBeNull();
});
