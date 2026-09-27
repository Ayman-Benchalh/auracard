<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS, Socialite providers and more.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Socialite OAuth Providers
    |--------------------------------------------------------------------------
    |
    | Google, Facebook, and Instagram OAuth integrations.
    | Credentials are read exclusively from environment variables.
    |
    */

    'google' => [
        'enabled' => (bool) env('SOCIAL_LOGIN_GOOGLE_ENABLED', true),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    'facebook' => [
        'enabled' => (bool) env('SOCIAL_LOGIN_FACEBOOK_ENABLED', true),
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', env('APP_URL') . '/auth/facebook/callback'),
    ],

    'instagram' => [
        // Meta's Instagram Basic Display API was deprecated in Dec 2024.
        // Current Meta Login for Instagram / Graph API strictly targets eligible professional accounts.
        // Kept disabled by default until dedicated Meta Professional setup is verified.
        'enabled' => (bool) env('SOCIAL_LOGIN_INSTAGRAM_ENABLED', false),
        'client_id' => env('INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
        'redirect' => env('INSTAGRAM_REDIRECT_URI', env('APP_URL') . '/auth/instagram/callback'),
    ],

];
