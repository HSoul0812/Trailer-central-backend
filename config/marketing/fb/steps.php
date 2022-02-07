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
        'login-fb',
        'goto-new-listing',
        'start-posting'
    ])),


    // Define Selectors By Step
    'selectors' => [
        'start-script' => env('FB_MARKETING_STEP_SELECTORS_START', 'common'),
        'stop-script' => env('FB_MARKETING_STEP_SELECTORS_STOP', 'common'),
        'login-fb' => env('FB_MARKETING_STEP_SELECTORS_LOGIN', 'common,login'),
        'goto-new-listing' => env('FB_MARKETING_STEP_SELECTORS_GOTO', 'common,listings'),
        'start-posting' => env('FB_MARKETING_STEP_SELECTORS_POSTING', 'common,posting.vehicle')
    ],


    // Define Log Messages By Step
    'logs' => [
        'start-script' => env('FB_MARKETING_STEP_LOG_START', 'Started Facebook Marketplace Extension script.'),
        'stop-script' => env('FB_MARKETING_STEP_LOG_STOP', 'Stopped Facebook Marketplace Extension script.'),
        'login-fb' => env('FB_MARKETING_STEP_LOG_LOGIN', 'Logging in to Facebook.'),
        'goto-new-listing' => env('FB_MARKETING_STEP_LOG_GOTO', 'Preparing to create a new listing with the ID #:inventoryId.'),
        'start-posting' => env('FB_MARKETING_STEP_LOG_POSTING', 'Starting posting to :action: a listing with the ID #:inventoryId.')
    ]
];