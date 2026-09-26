<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Marketing Image Generation Driver
    |--------------------------------------------------------------------------
    |
    | Supported drivers: 'openai', 'stability', 'none'
    | If 'none' or no API key is provided, the system uses high-fidelity
    | local SVG/vector fallback generation.
    |
    */
    'driver' => env('IMAGE_GENERATION_DRIVER', 'none'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_IMAGE_MODEL', 'dall-e-3'),
        'quality' => 'standard',
        'size' => '1024x1024',
    ],

    'stability' => [
        'api_key' => env('STABILITY_API_KEY'),
        'engine_id' => 'stable-diffusion-xl-1024-v1-0',
    ],

    'disk' => env('MARKETING_STORAGE_DISK', 'public'),

    'path' => 'marketing-assets',
];
