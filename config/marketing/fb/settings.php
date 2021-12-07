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

    // Primary Action to Start With
    'action' => env('FB_MARKETING_SETTING_ACTION', 'start-script'),


    // Define Facebook URL's
    'urls' => [
        'cookie' => env('FB_MARKETING_SETTING_URL_COOKIE', 'https://www.facebook.com'),
        'login' => env('FB_MARKETING_SETTING_URL_LOGIN', 'https://www.facebook.com/login'),
        'createVehicle' => env('FB_MARKETING_SETTING_URL_VEHICLE', 'https://www.facebook.com/marketplace/create/vehicle')
    ]
];