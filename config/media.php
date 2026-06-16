<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Media Provider
    |--------------------------------------------------------------------------
    |
    | Selects the active provider used by the MediaService. Keep this set to
    | "local" until the Supabase migration is approved for production.
    |
    */
    'default' => env('MEDIA_PROVIDER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Local Provider
    |--------------------------------------------------------------------------
    |
    | Preserves the current Laravel public disk behavior.
    |
    */
    'local' => [
        'disk' => env('MEDIA_LOCAL_DISK', 'public'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Supabase Provider
    |--------------------------------------------------------------------------
    |
    | Configuration for the future Supabase-backed media provider.
    |
    */
    'supabase' => [
        'url' => env('SUPABASE_URL', ''),
        'service_role_key' => env('SUPABASE_SERVICE_ROLE_KEY', ''),
        'public_bucket' => env('SUPABASE_PUBLIC_BUCKET', env('SUPABASE_BUCKET', 'yumeat-assets')),
        'storage_path' => env('SUPABASE_STORAGE_PATH', 'storage/v1/object'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Future Provider Placeholders
    |--------------------------------------------------------------------------
    |
    | These settings keep the media layer ready for future S3 or R2 providers
    | without changing the database contract or application code again.
    |
    */
    's3' => [
        'bucket' => env('MEDIA_S3_BUCKET', env('AWS_BUCKET', '')),
        'region' => env('MEDIA_S3_REGION', env('AWS_DEFAULT_REGION', '')),
        'url' => env('MEDIA_S3_URL', env('AWS_URL', '')),
        'endpoint' => env('MEDIA_S3_ENDPOINT', env('AWS_ENDPOINT', '')),
        'use_path_style_endpoint' => env('MEDIA_S3_USE_PATH_STYLE_ENDPOINT', env('AWS_USE_PATH_STYLE_ENDPOINT', false)),
    ],

    'r2' => [
        'bucket' => env('MEDIA_R2_BUCKET', env('AWS_BUCKET', '')),
        'account_id' => env('MEDIA_R2_ACCOUNT_ID', ''),
        'access_key_id' => env('MEDIA_R2_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID', '')),
        'secret_access_key' => env('MEDIA_R2_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY', '')),
        'endpoint' => env('MEDIA_R2_ENDPOINT', env('AWS_ENDPOINT', '')),
        'public_url' => env('MEDIA_R2_PUBLIC_URL', env('AWS_URL', '')),
    ],
];
