<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Facebook
    |--------------------------------------------------------------------------
    |
    | Here are the Facebook App Details
    |
    */

    // Settings for Marketing FB App
    'marketing'            => [
        // Chat App Key Settings
        'app'             => [
            'id'     => env('FB_SDK_APP_ID', ''),
            'secret' => env('FB_SDK_APP_SECRET', ''),
        ],

        // Marketing Scopes
        'scopes' => env('FB_SDK_SCOPES', 'email, public_profile, catalog_management, business_management'),
    ],

    // Settings for Chat FB App
    'chat'            => [
        // Chat App Key Settings
        'app'             => [
            'id'     => env('FB_CHAT_APP_ID', ''),
            'secret' => env('FB_CHAT_APP_SECRET', ''),
        ],

        // Chat Scopes
        'scopes' => env('FB_CHAT_SCOPES', 'email, pages_show_list, pages_read_engagement, pages_messaging, pages_manage_metadata'),
    ],

    // Catalog Settings
    'catalog'         => [
        'domain' => env('FB_CATALOG_DEFAULT_DOMAIN')
    ]
];