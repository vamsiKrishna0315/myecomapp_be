<?php

declare(strict_types=1);

use App\Models\FlashBanner;
use App\Models\MetaTag;
use App\Models\Page;
use App\Models\WhyUs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('returns canonical media url fields from the site data api', function (): void {
    Config::set('media.default', 'local');
    Config::set('media.local.disk', 'public');
    Storage::fake('public');

    WhyUs::query()->create([
        'title' => 'Farm Fresh',
        'image' => 'why-us/farm-fresh.jpg',
        'status' => true,
        'show_live' => true,
    ]);

    FlashBanner::query()->create([
        'name' => 'Weekend Deal',
        'image' => 'flash-banners/weekend-deal.jpg',
        'status' => true,
        'is_live' => true,
    ]);

    $page = Page::query()->create([
        'title' => 'Home',
        'slug' => '/',
        'type' => 'page',
        'status' => true,
    ]);

    MetaTag::query()->create([
        'metaable_id' => $page->id,
        'metaable_type' => Page::class,
        'meta_title' => 'Home',
        'og_image' => 'seo/og-home.jpg',
        'twitter_image' => 'seo/twitter-home.jpg',
        'status' => true,
    ]);

    $response = $this->getJson('/api/v1/customer/site/site-data');

    $response->assertSuccessful()
        ->assertJsonPath('data.why_us.0.image_url', Storage::disk('public')->url('why-us/farm-fresh.jpg'))
        ->assertJsonPath('data.flash_banners.image_url', Storage::disk('public')->url('flash-banners/weekend-deal.jpg'))
        ->assertJsonPath('data.meta_tags.0.og_image_url', Storage::disk('public')->url('seo/og-home.jpg'))
        ->assertJsonPath('data.meta_tags.0.twitter_image_url', Storage::disk('public')->url('seo/twitter-home.jpg'))
        ->assertJsonPath('data.meta_tags.0.seo.openGraph.image', Storage::disk('public')->url('seo/og-home.jpg'))
        ->assertJsonPath('data.meta_tags.0.seo.twitter.image', Storage::disk('public')->url('seo/twitter-home.jpg'));
});
