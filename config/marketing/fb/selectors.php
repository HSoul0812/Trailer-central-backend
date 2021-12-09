<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Facebook Marketplace Selectors
    |--------------------------------------------------------------------------
    |
    | All jQuery Selectors for the Complete Facebook Marketplace Process
    |
    */

    // Primary Selectors (Required on All/Most Pages)
    'common' => [
        'accountMenu' => env('FB_MARKETING_SELECTORS_COMMON_ACCOUNT', '*[role=navigation] *[aria-label="Account"]'),
        'logout' => env('FB_MARKETING_SELECTORS_COMMON_LOGOUT', '*[aria-label="Account"][role=dialog] > div > div > div > div > div > div > div > :nth-child(5) div[data-visualcompletion] > *[role=button]')
    ],

    // Login Selectors
    'login' => [
        'email' => env('FB_MARKETING_SELECTORS_LOGIN_EMAIL', '#email'),
        'password' => env('FB_MARKETING_SELECTORS_LOGIN_PASS', '#password'),
        'loginButton' => env('FB_MARKETING_SELECTORS_LOGIN_BTN', '#loginButton')
    ],

    // Posting
    'posting' => [
        // Vehicle Posting
        'vehicle' => [
            'typeDropdown' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPE', '[aria-label="Vehicle type"]')
        ]
    ]
];