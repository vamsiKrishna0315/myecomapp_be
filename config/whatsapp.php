<?php

declare(strict_types=1);

return [
    'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com'),
    'api_version' => env('WHATSAPP_API_VERSION', 'v22.0'),
    'token' => env('WHATSAPP_TOKEN'),
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN'),
    'default_language' => env('WHATSAPP_DEFAULT_LANGUAGE', 'en_US'),
    'queue' => env('WHATSAPP_QUEUE', 'whatsapp'),
    'timeout' => (int) env('WHATSAPP_TIMEOUT', 15),
    'templates' => [
        'hello_world' => env('WHATSAPP_HELLO_WORLD_TEMPLATE', 'hello_world'),
        'otp' => env('WHATSAPP_OTP_TEMPLATE', 'otp_verification'),
    ],
    'http_retry' => [
        'times' => (int) env('WHATSAPP_HTTP_RETRY_TIMES', 3),
        'sleep_milliseconds' => (int) env('WHATSAPP_HTTP_RETRY_SLEEP_MS', 1000),
    ],
    'job_retry' => [
        'tries' => (int) env('WHATSAPP_JOB_TRIES', 3),
        'timeout' => (int) env('WHATSAPP_JOB_TIMEOUT', 30),
        'max_exceptions' => (int) env('WHATSAPP_JOB_MAX_EXCEPTIONS', 3),
        'backoff' => array_map(
            static fn (string $value): int => (int) mb_trim($value),
            explode(',', (string) env('WHATSAPP_JOB_BACKOFF', '30,60,120')),
        ),
    ],
];
