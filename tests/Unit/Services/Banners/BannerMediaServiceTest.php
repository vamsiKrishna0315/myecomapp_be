<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Banners;

use App\Models\Banner;
use App\Observers\BannerObserver;
use App\Services\Banners\BannerMediaService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class BannerMediaServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('media.local.disk', 'public');
        Config::set('media.supabase.url', 'https://project.supabase.co');
        Config::set('media.supabase.service_role_key', 'service-role-key');
        Config::set('media.supabase.public_bucket', 'yumeat-assets');
        Config::set('media.supabase.storage_path', 'storage/v1/object');
    }

    public function test_it_resolves_banner_paths_through_the_active_media_provider(): void
    {
        Config::set('media.default', 'supabase');

        $service = app(BannerMediaService::class);

        $this->assertSame(
            'https://project.supabase.co/storage/v1/object/public/yumeat-assets/banners/home-banner.jpg',
            $service->bannerPathUrl('banners/home-banner.jpg')
        );
    }

    public function test_it_returns_only_active_live_banners_for_site_data(): void
    {
        Banner::create([
            'banner_name' => 'Visible banner',
            'banner_path' => 'banners/visible.jpg',
            'redirect_link' => null,
            'show_live' => 1,
            'status' => 1,
        ]);

        Banner::create([
            'banner_name' => 'Hidden banner',
            'banner_path' => 'banners/hidden.jpg',
            'redirect_link' => null,
            'show_live' => 0,
            'status' => 1,
        ]);

        Banner::create([
            'banner_name' => 'Inactive banner',
            'banner_path' => 'banners/inactive.jpg',
            'redirect_link' => null,
            'show_live' => 1,
            'status' => 0,
        ]);

        $banners = app(BannerMediaService::class)->activeBanners();

        $this->assertCount(1, $banners);
        $this->assertSame('Visible banner', $banners->first()?->banner_name);
    }

    public function test_it_deletes_banner_path_when_the_banner_is_deleted(): void
    {
        Config::set('media.default', 'local');
        Storage::fake('public');
        Storage::disk('public')->put('banners/to-delete.jpg', 'banner-content');

        $banner = Banner::create([
            'banner_name' => 'Delete me',
            'banner_path' => 'banners/to-delete.jpg',
            'redirect_link' => null,
            'show_live' => 1,
            'status' => 1,
        ]);

        app(BannerObserver::class)->deleted($banner);

        Storage::disk('public')->assertMissing('banners/to-delete.jpg');
    }
}
