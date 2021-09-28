<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Facebook API
    |--------------------------------------------------------------------------
    |
    | Here are the Facebook API details.
    |
    */

    // App Key Settings
    'app'             => [
        'id'     => env('FB_SDK_APP_ID', ''),
        'secret' => env('FB_SDK_APP_SECRET', ''),
    ],

    // Catalog Settings
    'catalog'         => [
        'domain' => env('FB_CATALOG_DEFAULT_DOMAIN')
    ]
];