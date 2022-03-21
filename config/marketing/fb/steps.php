<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Facebook Marketplace Steps
    |--------------------------------------------------------------------------
    |
    | All steps currently in the extension, and other settings related to steps.
    |
    */

    // Complete List of Available Steps
    'list' => env('FB_MARKETING_STEPS', implode(",", [
        'start-script',
        'stop-script',
        'verify-inventory',
        'login-fb',
        'login-validate',
        'goto-marketing',
        'goto-new-listing',
        'start-posting',
        'get-listing-id',
        'close-and-clear-cookies'
    ])),


    // Define Selectors By Step
    'selectors' => [
        'start-script' => env('FB_MARKETING_STEP_SELECTORS_START', 'common'),
        'stop-script' => env('FB_MARKETING_STEP_SELECTORS_STOP', 'common'),
        'verify-inventory' => env('FB_MARKETING_STEP_SELECTORS_INVENTORY', 'common'),
        'login-fb' => env('FB_MARKETING_STEP_SELECTORS_LOGIN', 'common,login'),
        'login-validate' => env('FB_MARKETING_STEP_SELECTORS_LOGIN_VALIDATE', 'common,login'),
        'goto-marketing' => env('FB_MARKETING_STEP_SELECTORS_MARKETPLACE', 'common,listings'),
        'goto-new-listing' => env('FB_MARKETING_STEP_SELECTORS_NEW_LISTING', 'common,listings'),
        'start-posting' => env('FB_MARKETING_STEP_SELECTORS_POSTING', 'common,posting.vehicle'),
        'get-listing-id' => env('FB_MARKETING_STEP_SELECTORS_LISTING_ID', 'common,listings'),
        'close-and-clear-cookies' => env('FB_MARKETING_STEP_SELECTORS_CLOSE', 'common')
    ],


    // Define Log Messages By Step
    'logs' => [
        'start-script' => env('FB_MARKETING_STEP_LOG_START', 'Started Facebook Marketplace Extension script.'),
        'stop-script' => env('FB_MARKETING_STEP_LOG_STOP', 'Stopped Facebook Marketplace Extension script.'),
        'verify-inventory' => env('FB_MARKETING_STEP_LOG_INVENTORY', 'Verify current dealer integration has inventory to post.'),
        'login-fb' => env('FB_MARKETING_STEP_LOG_LOGIN', 'Logging in to Facebook.'),
        'login-validate' => env('FB_MARKETING_STEP_LOG_LOGIN_VALIDATE', 'Validating dealer account successfully logged in.'),
        'goto-marketing' => env('FB_MARKETING_STEP_LOG_MARKETING', 'Validating we can posting to Facebook Marketplace.'),
        'goto-new-listing' => env('FB_MARKETING_STEP_LOG_NEW_LISTING', 'Preparing to create a new listing with the ID #:inventoryId.'),
        'start-posting' => env('FB_MARKETING_STEP_LOG_POSTING', 'Starting posting to :action a listing with the ID #:inventoryId.'),
        'get-listing-id' => env('FB_MARKETING_STEP_LOG_LISTING_ID', 'Successfully :actiond a listing with the ID #:inventoryId, getting facebook listing id.'),
        'close-and-clear-cookies' => env('FB_MARKETING_STEP_LOG_CLOSE', 'Finished posting all inventory for the current dealer integration, moving to next one.')
    ]
];