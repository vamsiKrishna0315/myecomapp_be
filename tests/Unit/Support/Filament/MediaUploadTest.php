<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Filament;

use App\Enums\MediaCategory;
use App\Support\Filament\MediaUpload;
use Closure;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use ReflectionProperty;
use Tests\TestCase;

final class MediaUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('media.local.disk', 'public');
        Config::set('media.default', 'local');
        Storage::fake('public');
        Storage::fake('tmp-for-tests');
    }

    public function test_it_configures_file_uploads_without_directories(): void
    {
        $component = MediaUpload::configure(
            FileUpload::make('primary_image')->image(),
            MediaCategory::Products
        );

        $this->assertSame('public', $component->getDiskName());
        $this->assertNull($component->getDirectory());
        $this->assertFalse($component->shouldFetchFileInformation());

        $save = $this->componentCallback($component, 'saveUploadedFileUsing');
        $delete = $this->componentCallback($component, 'deleteUploadedFileUsing');
        $file = $this->temporaryFile('primary-image-hash=primary-image-mimeType=image_jpeg.jpg');

        $path = $save($component, $file);

        $this->assertStringStartsWith('products/', $path);
        Storage::disk('public')->assertExists($path);

        $delete($path);

        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_it_returns_preview_metadata_from_media_service_url(): void
    {
        $component = MediaUpload::configure(
            FileUpload::make('primary_image')->image(),
            MediaCategory::Products
        );

        $getUploadedFile = $this->componentCallback($component, 'getUploadedFileUsing');
        $path = 'products/demo-image.jpg';

        $metadata = $getUploadedFile($component, $path, null);

        $this->assertSame('demo-image.jpg', $metadata['name']);
        $this->assertSame(0, $metadata['size']);
        $this->assertNull($metadata['type']);
        $this->assertSame(app(\App\Services\Media\MediaService::class)->publicUrl($path), $metadata['url']);
    }

    public function test_it_supports_multiple_files_with_the_same_category_prefix(): void
    {
        $component = MediaUpload::configure(
            FileUpload::make('images')->multiple()->image(),
            MediaCategory::Products
        );

        $this->assertTrue($component->isMultiple());

        $save = $this->componentCallback($component, 'saveUploadedFileUsing');
        $firstPath = $save($component, $this->temporaryFile('gallery-one-hash=gallery-one-mimeType=image_jpeg.jpg'));
        $secondPath = $save($component, $this->temporaryFile('gallery-two-hash=gallery-two-mimeType=image_jpeg.jpg'));

        $this->assertStringStartsWith('products/', $firstPath);
        $this->assertStringStartsWith('products/', $secondPath);
        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertExists($firstPath);
        Storage::disk('public')->assertExists($secondPath);
    }

    public function test_it_supports_common_public_media_categories(): void
    {
        $cases = [
            [MediaCategory::Products, 'primary_image', 'products'],
            [MediaCategory::Categories, 'category_image', 'categories'],
            [MediaCategory::Banners, 'banner_path', 'banners'],
            [MediaCategory::FlashBanners, 'image', 'flash-banners'],
            [MediaCategory::WhyUs, 'image', 'why-us'],
            [MediaCategory::Stores, 'logo', 'stores'],
            [MediaCategory::ProductCuts, 'image', 'product-cuts'],
        ];

        foreach ($cases as [$category, $field, $prefix]) {
            $component = MediaUpload::configure(
                FileUpload::make($field)->image(),
                $category
            );

            $save = $this->componentCallback($component, 'saveUploadedFileUsing');
            $path = $save($component, $this->temporaryFile("{$field}-hash={$field}-mimeType=image_jpeg.jpg"));

            $this->assertStringStartsWith($prefix.'/', $path);
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_it_supports_private_categories_for_driver_documents(): void
    {
        $component = MediaUpload::configure(
            FileUpload::make('driver_license_image'),
            MediaCategory::Licenses
        );

        $save = $this->componentCallback($component, 'saveUploadedFileUsing');
        $path = $save($component, $this->temporaryFile('license-hash=license-mimeType=application_pdf.pdf'));

        $this->assertStringStartsWith('licenses/', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_it_supports_user_document_uploads(): void
    {
        $component = MediaUpload::configure(
            FileUpload::make('address_proof'),
            MediaCategory::UserDocuments
        );

        $save = $this->componentCallback($component, 'saveUploadedFileUsing');
        $path = $save($component, $this->temporaryFile('proof-hash=proof-mimeType=application_pdf.pdf'));

        $this->assertStringStartsWith('user-documents/', $path);
        Storage::disk('public')->assertExists($path);
    }

    private function componentCallback(BaseFileUpload $component, string $property): Closure
    {
        $reflection = new ReflectionProperty($component, $property);

        return $reflection->getValue($component);
    }

    private function temporaryFile(string $filename): TemporaryUploadedFile
    {
        Storage::disk('tmp-for-tests')->put('livewire-tmp/'.$filename, 'temporary-upload');

        return TemporaryUploadedFile::createFromLivewire($filename);
    }
}
