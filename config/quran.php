<?php

return [
    'provider' => env('QURAN_PROVIDER', 'ummahapi'),
    'cache_store' => env('QURAN_CACHE_STORE'),
    'cache_ttl' => (int) env('QURAN_CACHE_TTL', 604800),
    'providers' => [
        'equran' => [
            'base_url' => env('EQURAN_BASE_URL', 'https://equran.id'),
            'timeout' => (int) env('EQURAN_TIMEOUT', 10),
        ],
        'ummahapi' => [
            'base_url' => env('UMMAH_API_BASE_URL', 'https://ummahapi.com'),
            'timeout' => (int) env('UMMAH_API_TIMEOUT', 10),
        ],
    ],
];
