<?php

return [
    'name' => env('APP_NAME', 'LegalEase'),
    'env' => env('APP_ENV', 'local'),
    'debug' => (bool) env('APP_DEBUG', true),
    'url' => env('APP_URL', 'http://127.0.0.1:8000'),
    'timezone' => 'Asia/Ho_Chi_Minh',
    'locale' => env('APP_LOCALE', 'vi'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => 'vi_VN',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => array_filter(explode(',', (string) env('APP_PREVIOUS_KEYS', ''))),
    'maintenance' => [
        'driver' => 'file',
        'store' => 'database',
    ],
];
