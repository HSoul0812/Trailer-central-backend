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
        'choose-vehicle-type',
        'start-posting',
        'skip-posting',
        'get-listing-id',
        'close-and-clear-cookies',
        'timed-out'
    ])),


    // Complete List of Available Errors
    'errors' => env('FB_MARKETING_ERRORS', implode(",", [
        'no-error',
        'unknown',
        // Authentication
        'account-disabled',
        'account-locked',
        'auth-fail',
        'email-verification',
        'login-approval',
        'login-failed',
        'login-invalid',
        'two-factor-auth',
        'two-factor-failed',
        // Connectivity
        'missing-tunnel',
        'offline-tunnel',
        'page-unavailable',
        'slow-tunnel',
        'timed-out',
        // Inventory
        'missing-inventory',
        // Marketplace
        'final-account-review',
        'marketplace-blocked',
        'marketplace-inaccessible',
        'temp-blocked',
        // Posting
        'failed-post',
        'flagged-post',
        'limit-reached',
        'location-invalid',
    ])),

    'errors_title' => [
        'no-error' => "No Error",
        'missing-tunnel' => "Tunnel Client App Not Installed",
        'offline-tunnel' => "Tunnel Client App Offline",
        'slow-tunnel' => "Tunnel Client App Slow Internet Connection",
        'missing-inventory' => "Inventory Missing",
        'login-failed' => "Login Failed",
        'login-invalid' => "Login Invalid",
        'email-verification' => "Email Verification",
        'login-approval' => "Login Approval",
        'two-factor-auth' => "Two Factor Authentication Required",
        'two-factor-failed' => "Two Factor Authentication Failed",
        'account-locked' => "Facebook Account locked",
        'account-disabled' => "Facebook Account disabled",
        'temp-blocked' => "Facebook Account temporary blocked",
        'page-unavailable' => "Facebook Page Unavailable",
        'marketplace-inaccessible' => "Marketplace Not Available",
        'marketplace-blocked' => "Market Place blocked",
        'final-account-review' => "Final Account Review",
        'limit-reached' => "Posting Limit Reached Temporary",
        'failed-post' => "Failed Post",
        'flagged-post' => "Flagged Post"
    ],


    // Define Selectors By Step
    'selectors' => [
        'start-script' => env('FB_MARKETING_STEP_SELECTORS_START', 'common'),
        'stop-script' => env('FB_MARKETING_STEP_SELECTORS_STOP', 'common'),
        'verify-inventory' => env('FB_MARKETING_STEP_SELECTORS_INVENTORY', 'common'),
        'login-fb' => env('FB_MARKETING_STEP_SELECTORS_LOGIN', 'common,login'),
        'login-validate' => env('FB_MARKETING_STEP_SELECTORS_LOGIN_VALIDATE', 'common,login,loginValidate'),
        'code-validate' => env('FB_MARKETING_STEP_SELECTORS_LOGIN_VALIDATE', 'common,login,loginValidate'),
        'goto-marketing' => env('FB_MARKETING_STEP_SELECTORS_MARKETPLACE', 'common,listings'),
        'goto-new-listing' => env('FB_MARKETING_STEP_SELECTORS_NEW_LISTING', 'common,listings'),
        'choose-vehicle-type' => env('FB_MARKETING_STEP_SELECTORS_VEHICLE', 'common,posting.common,posting.vehicle'),
        'start-posting' => env('FB_MARKETING_STEP_SELECTORS_POSTING', 'common,posting.common,posting.vehicle'),
        'skip-posting' => env('FB_MARKETING_STEP_SELECTORS_POSTING_SKIP', 'common,posting.common'),
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
        'code-validate' => env('FB_MARKETING_STEP_LOG_LOGIN_VALIDATE', 'Filling out request approval form for dealer after login.'),
        'goto-marketing' => env('FB_MARKETING_STEP_LOG_MARKETING', 'Validating we can posting to Facebook Marketplace.'),
        'goto-new-listing' => env('FB_MARKETING_STEP_LOG_NEW_LISTING', 'Preparing to create a new listing with the ID #:inventoryId.'),
        'choose-vehicle-type' => env('FB_MARKETING_STEP_LOG_VEHICLE', 'Choosing vehicle type to :action a listing with the ID #:inventoryId.'),
        'start-posting' => env('FB_MARKETING_STEP_LOG_POSTING', 'Starting posting to :action a listing with the ID #:inventoryId.'),
        'skip-posting' => env('FB_MARKETING_STEP_LOG_POSTING_SKIP', 'An error occured trying to :action a listing with the ID #:inventoryId, skipping to next item.'),
        'get-listing-id' => env('FB_MARKETING_STEP_LOG_LISTING_ID', 'Successfully :actiond a listing with the ID #:inventoryId, getting facebook listing id.'),
        'close-and-clear-cookies' => env('FB_MARKETING_STEP_LOG_CLOSE', 'Finished posting all inventory for the current dealer integration, moving to next one.')
    ]
];
