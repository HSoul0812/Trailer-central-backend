<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'here' => [
        'key' => env('HERE_API_KEY', ''),
    ],

    'trailercentral' => [
        'api' => env('TC_API_URL', 'https://api.v1.staging.trailercentral.com/api/'),
        'access_token' => env('TC_API_ACCESS_TOKEN', 'f3c74ad00f954cc698face16ff78d791'),
        'tt_website_id' => env('TC_API_TT_ID', '284'),
        'integration_access_token' => env('TC_INTEGRATION_ACCESS_TOKEN'),
    ],

    'tomtom' => [
        'key' => env('TOMTOM_API_KEY', ''),
    ],

    'google' => [
        'map' => [
            'key' => env('GOOGLE_MAP_API_KEY', ''),
        ],
        'captcha' => [
            'key' => env('GOOGLE_CAPTCHA_API_KEY', ''),
            'human_threshold' => env('GOOGLE_CAPTCHA_HUMAN_THRESHOLD', 0.7),
        ],
        'client_id' => env('GOOGLE_AUTH_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_AUTH_CLIENT_SECRET', ''),
        'redirect' => '/api/user/auth/google/callback',
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_AUTH_CLIENT_ID', ''),
        'client_secret' => env('FACEBOOK_AUTH_CLIENT_SECRET', ''),
        'redirect' => '/api/user/auth/facebook/callback',
    ],
    'stripe' => [
        'public_key' => env('STRIPE_PUBLIC_KEY', ''),
        'secret_key' => env('STRIPE_SECRET_KEY', ''),
        'webhook_secret_key' => env('STRIPE_WEBHOOK_SECRET_KEY', ''),
    ],
];
