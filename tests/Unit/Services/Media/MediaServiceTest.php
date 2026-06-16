<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Media;

use App\Enums\MediaCategory;
use App\Services\Media\MediaProviderInterface;
use App\Services\Media\MediaService;
use App\Services\Media\Providers\LocalProvider;
use App\Services\Media\Providers\SupabaseProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MediaServiceTest extends TestCase
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

    public function test_it_uses_the_local_provider_by_default_and_matches_public_disk_behavior(): void
    {
        Config::set('media.default', 'local');
        Storage::fake('public');

        $provider = app(MediaProviderInterface::class);
        $service = app(MediaService::class);

        $this->assertInstanceOf(LocalProvider::class, $provider);
        $this->assertSame(
            [
                'products',
                'categories',
                'banners',
                'flash-banners',
                'why-us',
                'stores',
                'cut-types',
                'product-cuts',
                'seo',
                'reviews',
            ],
            $service->publicCategories()
        );
        $this->assertSame(
            [
                'drivers',
                'licenses',
                'insurance',
                'registrations',
                'user-documents',
            ],
            $service->privateCategories()
        );

        $file = UploadedFile::fake()->createWithContent('example.jpg', 'local-file-content');
        $path = $service->upload($file, MediaCategory::Products, 'example.jpg');

        $this->assertSame('products/example.jpg', $path);
        Storage::disk('public')->assertExists($path);
        $this->assertSame(Storage::disk('public')->url($path), $service->publicUrl($path));
        $this->assertTrue($service->exists($path));
        $this->assertTrue($service->delete($path));
        $this->assertFalse($service->exists($path));
    }

    public function test_it_selects_supabase_and_builds_supabase_urls_and_requests(): void
    {
        Config::set('media.default', 'supabase');

        $provider = app(MediaProviderInterface::class);
        $service = app(MediaService::class);

        $this->assertInstanceOf(SupabaseProvider::class, $provider);
        $this->assertSame(
            'https://project.supabase.co/storage/v1/object/public/yumeat-assets/products/chicken.jpg',
            $service->publicUrl('products/chicken.jpg')
        );
        $this->assertSame(
            $service->publicUrl('products/chicken.jpg'),
            $service->publicUrl('/products/chicken.jpg')
        );

        $seen = [];

        Http::fake(function ($request) use (&$seen) {
            $seen[] = [$request->method(), $request->url()];

            return Http::response([], 200);
        });

        $file = UploadedFile::fake()->createWithContent('upload.jpg', 'supabase-file-content');
        $path = $service->upload($file, MediaCategory::Products, 'upload.jpg');

        $this->assertSame('products/upload.jpg', $path);
        $this->assertTrue($service->exists($path));
        $this->assertTrue($service->delete($path));

        $this->assertTrue(in_array(['POST', 'https://project.supabase.co/storage/v1/object/yumeat-assets/products/upload.jpg'], $seen, true));
        $this->assertTrue(in_array(['HEAD', 'https://project.supabase.co/storage/v1/object/yumeat-assets/products/upload.jpg'], $seen, true));
        $this->assertTrue(in_array(['DELETE', 'https://project.supabase.co/storage/v1/object/yumeat-assets/products/upload.jpg'], $seen, true));
    }

    public function test_it_rejects_traversal_segments_in_media_paths(): void
    {
        Config::set('media.default', 'local');
        Storage::fake('public');

        $this->expectException(\InvalidArgumentException::class);

        app(MediaService::class)->publicUrl('../secret.jpg');
    }

    public function test_supabase_rejects_traversal_segments_in_media_paths(): void
    {
        Config::set('media.default', 'supabase');

        $this->expectException(\InvalidArgumentException::class);

        app(MediaService::class)->publicUrl('../secret.jpg');
    }

    public function test_it_allows_private_categories_for_uploads_and_preserves_object_key_structure(): void
    {
        Config::set('media.default', 'local');
        Storage::fake('public');

        $path = app(MediaService::class)->upload(
            UploadedFile::fake()->createWithContent('driver-license.jpg', 'driver-file-content'),
            MediaCategory::Drivers
        );

        $this->assertStringStartsWith('drivers/', $path);
        Storage::disk('public')->assertExists($path);
    }
}
