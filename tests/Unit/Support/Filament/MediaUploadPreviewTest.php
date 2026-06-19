<?php

declare(strict_types=1);

use App\Enums\MediaCategory;
use App\Support\Filament\MediaUpload;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Config;

it('builds provider aware previews for existing uploaded files', function (): void {
    Config::set('media.default', 'supabase');
    Config::set('media.local.disk', 'public');
    Config::set('media.supabase.url', 'https://project.supabase.co');
    Config::set('media.supabase.service_role_key', 'service-role-key');
    Config::set('media.supabase.public_bucket', 'yumeat-assets');

    $component = MediaUpload::configure(
        FileUpload::make('primary_image')->image(),
        MediaCategory::Products
    );

    $getUploadedFile = componentCallback($component, 'getUploadedFileUsing');
    $uploadedFile = $getUploadedFile($component, 'products/chicken.jpg', null);

    expect($component->shouldFetchFileInformation())->toBeFalse()
        ->and($uploadedFile)->toBe([
            'name' => 'chicken.jpg',
            'size' => 0,
            'type' => null,
            'url' => 'https://project.supabase.co/storage/v1/object/public/yumeat-assets/products/chicken.jpg',
        ]);
});

it('uses stored file names for multiple uploaded file previews', function (): void {
    Config::set('media.default', 'supabase');
    Config::set('media.local.disk', 'public');
    Config::set('media.supabase.url', 'https://project.supabase.co');
    Config::set('media.supabase.service_role_key', 'service-role-key');
    Config::set('media.supabase.public_bucket', 'yumeat-assets');

    $component = MediaUpload::configure(
        FileUpload::make('images')->multiple()->image(),
        MediaCategory::Products
    );

    $getUploadedFile = componentCallback($component, 'getUploadedFileUsing');
    $uploadedFile = $getUploadedFile($component, 'products/gallery-1.jpg', [
        'products/gallery-1.jpg' => 'gallery-photo.jpg',
    ]);

    expect($uploadedFile)->toBe([
        'name' => 'gallery-photo.jpg',
        'size' => 0,
        'type' => null,
        'url' => 'https://project.supabase.co/storage/v1/object/public/yumeat-assets/products/gallery-1.jpg',
    ]);
});

function componentCallback(BaseFileUpload $component, string $property): Closure
{
    $reflection = new ReflectionProperty($component, $property);

    return $reflection->getValue($component);
}
