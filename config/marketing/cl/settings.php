<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Craigslist Marketplace Settings
    |--------------------------------------------------------------------------
    |
    | Common settings for the general extension and interface
    |
    */

    // Image Domain
    'images' => [
        'domain' => env('CLAPP_SETTINGS_IMAGE_DOMAIN', 'https://dealer-cdn.com')
    ],

    // Show on Website Exceptions
    'overrides' => [
        'showOnWebsite' => env('CLAPP_OVERRIDE_HIDDEN', '1147,8467,7206,7099,6181,5542,5540,5541,7439,5521,6461,7638,10232'),
        'statusAll' => env('CLAPP_OVERRIDE_STATUS_ALL', '5936,5840'),
        'statusOnOrder' => env('CLAPP_OVERRIDE_STATUS_ALL', '5251'),
    ]
];