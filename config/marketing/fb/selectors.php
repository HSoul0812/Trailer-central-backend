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

    // Listing
    'listings' => [
        // getToNewVehiclePageQs
        'marketplaceButton' => env('FB_MARKETING_SELECTORS_LISTINGS_MARKETPLACE_BUTTON', 'a[href*="facebook.com/marketplace/"]'),
        'createNewListingButton' => env('FB_MARKETING_SELECTORS_LISTINGS_NEW_BUTTON', 'a[href="/marketplace/create/"]'),
        'createNewVehicleListingButton' => env('FB_MARKETING_SELECTORS_LISTINGS_NEW_VEHICLE_BUTTON', 'a[href="/marketplace/create/vehicle/"]'),
        // listingPageQs
        'listingItemMoreMenu' => env('FB_MARKETING_SELECTORS_LISTINGS_MORE_BUTTON', '[aria-label="More"] i'),
        'firstItemLink' => env('FB_MARKETING_SELECTORS_LISTINGS_FIRST_ITEM', 'a[href^="https://www.facebook.com/marketplace/item/"]')
    ],

    // Posting
    'posting' => [
        // Vehicle Posting
        'vehicle' => [
            'vehicleTypeDropdown' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPE', '[aria-label="Vehicle type"]'),
            'vehicleTypeOptionsParent' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_MENU', 'div[role="menu"] span'),
            'vehicleType1' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPE_1', 'div[role="option"]:nth-child(1) span'),
            'vehicleType2' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPE_2', 'div[role="option"]:nth-child(2) span'),
            'vehicleType3' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPE_3', 'div[role="option"]:nth-child(3) span'),
            'vehicleType4' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPE_4', 'div[role="option"]:nth-child(4) span'),
            'vehicleType5' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPE_5', 'div[role="option"]:nth-child(5) span'),
            'vehicleType6' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPE_6', 'div[role="option"]:nth-child(6) span'),
            'vehicleType7' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPE_7', 'div[role="option"]:nth-child(7) span'),
            'vehicleType8' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPE_8', 'div[role="option"]:nth-child(8) span'),

            'vehicleImageInput' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_IMAGE', 'input[type="file"]'),
            'vehicleyearDropdown' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_YEAR', '[aria-label="Year"]'),
            'vehicleYearList' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_YEAR_LIST', '[role="option"]'),

            'vehicleMake' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_MAKE', '[aria-label="Make"] input'),
            'vehicleModel' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_MODEL', '[aria-label="Model"] input'),
            'vehiclePrice' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_PRICE', '[aria-label="Price"] input'),

            'vehicleExteriorColor' => explode(',', env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_EXTERIOR_COLOR', '[aria-label="Exterior color"],[aria-label="Exterior colour"]')),
            'vehicleExteriorColorList' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_EXTERIOR_COLOR_LIST', '[role="option"]'),

            'vehicleInteriorColor' => explode(',', env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_INTERIOR_COLOR', '[aria-label="Interior color"],[aria-label="Interior colour"]')),
            'vehicleInteriorColorList' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_INTERIOR_COLOR_LIST', '[role="option"]'),

            'vehicleFuelType' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_FUEL_TYPE', '[aria-label="Fuel typeello'),
            'vehicleFuelTypeList' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_FUEL_TYPE_LIST', '[role="option"]'),

            'vehicleDescription' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_DESCRIPTION', '[aria-label="Description"] textarea'),

            'vehicleLocationInput' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_LOCATION_INPUT', '[aria-label="Location"] input'),
            'vehicleSearchFirstOption' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_LOCATION_FIRST_OPTION', '[role="option"] span'),

            'vehicleNextButton' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_NEXT_BUTTON', '[aria-label="Next"]'),
            'vehiclePublishButton' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_PUBLISH_BUTTON', '[aria-label="Publish"]'),

            'vehiclePublishFailedDialog' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_FAILED_DIALOG', '[role="dialog"]'),
        ],
    ]
];