<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Facebook Marketplace Settings
    |--------------------------------------------------------------------------
    |
    | Common settings for the general extension
    |
    */

    // Image Domain
    'images' => [
        'domain' => env('FB_MARKETING_SETTINGS_IMAGE_DOMAIN', 'https://dealer-cdn.com')
    ],


    // Primary Action to Start With
    'action' => env('FB_MARKETING_SETTING_ACTION', 'start-script'),


    // Posting Limits
    'limit' => [
        'force' => env('FB_MARKETING_SETTING_LIMIT_FORCE', '1'),
        'listings' => env('FB_MARKETING_SETTING_LIMIT_LISTINGS', '3'),
        'hours' => env('FB_MARKETING_SETTING_LIMIT_HOURS', '24'),
        'errors' => env('FB_MARKETING_SETTING_LIMIT_ERRORS', '1'),
        'ttl' => env('FB_MARKETING_SETTING_LIMIT_TTL', ''),
    ],


    // Define Facebook URL's
    'urls' => [
        'cookie' => env('FB_MARKETING_SETTING_URL_COOKIE', 'https://www.facebook.com'),
        'root' => env('FB_MARKETING_SETTING_URL_ROOT', 'https://www.facebook.com'),
        'login' => env('FB_MARKETING_SETTING_URL_LOGIN', 'https://www.facebook.com/login'),
        'createVehicle' => env('FB_MARKETING_SETTING_URL_VEHICLE', 'https://www.facebook.com/marketplace/create/vehicle'),
        'marketplaceListing' => env('FB_MARKETING_SETTING_URL_LISTINGS', 'https://www.facebook.com/marketplace/you/selling')
    ],


    // Active Fields
    'fields' => [
        'page_url' => env('FB_MARKETING_SETTING_FIELDS_PAGE_URL', false),
        'tfa_types' => env('FB_MARKETING_SETTING_FIELDS_TFA_TYPES', implode(",", [
            'sms'
        ]))
    ]
];