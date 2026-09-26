<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AURA CARD Brand & Business Defaults
    |--------------------------------------------------------------------------
    */
    'brand' => [
        'name' => env('APP_NAME', 'AURA CARD'),
        'support_email' => env('AURACARD_SUPPORT_EMAIL', 'support@auracard.ma'),
        'support_whatsapp' => env('AURACARD_SUPPORT_WHATSAPP', '+212600000000'),
        'currency' => 'MAD',
    ],

    'pricing' => [
        'default_card_price_mad' => (float) env('AURACARD_CARD_PRICE_MAD', 150.00),
        'included_free_modification_credits' => (int) env('AURACARD_INCLUDED_MODIFICATION_CREDITS', 2),
    ],

    'locales' => [
        'supported' => ['ar', 'fr', 'en'],
        'default' => env('APP_LOCALE', 'fr'),
        'fallback' => env('APP_FALLBACK_LOCALE', 'en'),
        'directions' => [
            'ar' => 'rtl',
            'fr' => 'ltr',
            'en' => 'ltr',
        ],
    ],

    'limits' => [
        'max_avatar_kb' => (int) env('AURACARD_MAX_AVATAR_SIZE_KB', 2048),
        'max_cover_kb' => (int) env('AURACARD_MAX_COVER_SIZE_KB', 4096),
        'max_proof_kb' => (int) env('AURACARD_MAX_PROOF_SIZE_KB', 8192),
    ],
];
