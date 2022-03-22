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


    // TTL to Expire for Posting
    'ttl' => env('FB_MARKETING_SETTING_TTL', ''),


    // Define Facebook URL's
    'urls' => [
        'cookie' => env('FB_MARKETING_SETTING_URL_COOKIE', 'https://www.facebook.com'),
        'login' => env('FB_MARKETING_SETTING_URL_LOGIN', 'https://www.facebook.com/login'),
        'createVehicle' => env('FB_MARKETING_SETTING_URL_VEHICLE', 'https://www.facebook.com/marketplace/create/vehicle')
    ],


    // Active Fields
    'fields' => [
        'page_url' => env('FB_MARKETING_SETTING_FIELDS_PAGE_URL', false),
        'tfa_types' => env('FB_MARKETING_SETTING_FIELDS_TFA_TYPES', implode(",", [
            'default', 'sms'
        ]))
    ]
];