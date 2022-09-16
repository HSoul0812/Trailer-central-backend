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
        'logout' => env('FB_MARKETING_SELECTORS_COMMON_LOGOUT', '*[aria-label="Account"][role=dialog] > div > div > div > div > div > div > div > :nth-child(5) div[data-visualcompletion] > *[role=button]'),
        'detectAccountDisabledText' => env('FB_MARKETING_SELECTORS_DETECT_ACCOUNT_DISABLED', 'div > span:contains(\'Your account has been disabled\')'),
        'detectRequestAReviewButton' => env('FB_MARKETING_SELECTORS_DETECT_REQUEST_A_REVIEW_BUTTON', 'div[aria-label="Request a Review"]'),
        'detectTempBlock' => env('FB_MARKETING_SELECTORS_DETECT_BLOCK', 'div > h3:contains("You\'re Temporarily Blocked")'),
        'detectAccountLocked' => env('FB_MARKETING_SELECTORS_DETECT_ACCOUNT_LOCKED', 'div > span:contains("your account has been locked")'),
        'detectAccountLoginApprovalNeeded' => env('FB_MARKETING_SELECTORS_DETECT_ACCOUNT_LOGIN_APROVAL_NEEDED', 'span:contains("Login aproval needed")'),
        'detectPageUnavailable' => env('FB_MARKETING_SELECTORS_DETECT_PAGE_UNAVAILABLE', 'div > span:contains("This Page Isn\'t Available Right Now")'),
        'reloadPageButton' => env('FB_MARKETING_SELECTORS_DETECT_PAGE_UNAVAILABLE', 'div > span > span:contains("Reload Page")')
    ],

    // Login Selectors
    'login' => [
        'email' => env('FB_MARKETING_SELECTORS_LOGIN_EMAIL', '#email'),
        'password' => env('FB_MARKETING_SELECTORS_LOGIN_PASS', '#pass'),
        'loginButton' => env('FB_MARKETING_SELECTORS_LOGIN_BTN', '#loginbutton'),
        'detectIncorrectPassText' => env('FB_MARKETING_SELECTORS_DETECT_INVALID_PASS', "div:contains('The password you’ve entered is incorrect.'"),
        'detectInvalidCredentialsText' => env('FB_MARKETING_SELECTORS_DETECT_INVALID_USERNAME', "div:contains('The email address or mobile number you entered')"),
        'detectEmaiilValidationText' =>  env('FB_MARKETING_SELECTORS_DETECT_EMAIL_VERIFY', 'div:contains(\'Enter security code\')'),
        'detectOldPassText' =>  env('FB_MARKETING_SELECTORS_DETECT_OLD_PASSWORD', 'div:contains(\'You Entered An Old Password\')'),
        'somethingWentWrong' =>  env('FB_MARKETING_SELECTORS_DETECT_SOMETHING_WENT_WRONG', 'div:contains(\'Sorry, something went wrong\')')
    ],

    // Login Validator
    'loginValidate' => [
        'detectTwoFactor' => env('FB_MARKETING_SELECTORS_VALIDATE_DETECT_TWO_FACTOR', 'div > strong:contains(\'Two-Factor Authentication Required\')'),
        'detectMissingMobileNumber' => env('FB_MARKETING_SELECTORS_VALIDATE_DETECT_MOBILE', '.mobileMirrorHeading:contains("Add a Mobile Number")'),
        'detectLoginValidate' => env('FB_MARKETING_SELECTORS_VALIDATE_DETECT_APPROVAL', 'div > strong:contains(\'Login approval needed\')'),
        'detectChooseOption' => env('FB_MARKETING_SELECTORS_VALIDATE_DETECT_OPTION', 'div > strong:contains(\'Choose an option\')'),
        'detectGetCode' => env('FB_MARKETING_SELECTORS_VALIDATE_DETECT_CODE', 'div > strong:contains(\'Get a code send to your email\')'),
        'detectEnterCode' => env('FB_MARKETING_SELECTORS_VALIDATE_DETECT_ENTER', 'div > strong:contains(\'Enter Code\')'),
        'twoFactorInput' => env('FB_MARKETING_SELECTORS_VALIDATE_INPUT_APPROVAL', 'input[@name=\'approvals_code\']'),
        'continueButton' => env('FB_MARKETING_SELECTORS_VALIDATE_BUTTON_CONTINUE', 'button[value="Continue"]'),
        'verifyMethodInput' => env('FB_MARKETING_SELECTORS_VALIDATE_INPUT_METHOD', 'input[@name=\'verification_method\' and @value=\'37\']'),
        'codeRequestInput' => env('FB_MARKETING_SELECTORS_VALIDATE_INPUT_REQUEST', 'input[@name=\'eindex\']:first'),
        'codeResponseInput' => env('FB_MARKETING_SELECTORS_VALIDATE_INPUT_RESPONSE', 'input[@name=\'captcha_response\']')
    ],

    // Listing
    'listings' => [
        // getToNewVehiclePageQs
        'facebookLogoButton' => env('FB_MARKETING_SELECTORS_LISTINGS_MARKETPLACE_LOGO', 'a[title="Go to Facebook home"]'),
        'marketplaceButton' => env('FB_MARKETING_SELECTORS_LISTINGS_MARKETPLACE_BUTTON', 'a[href*=\"facebook.com/marketplace/\"]:contains(\'Marketplace\')'),
        'marketplaceSidebar' => env('FB_MARKETING_SELECTORS_LISTINGS_MARKETPLACE_SIDEBAR', 'div[aria-label="Marketplace Sidebar"][role="Navigation"]'),
        'createNewListingShortButton' => env('FB_MARKETING_SELECTORS_LISTINGS_NEW_BUTTON_SHORT', '*[aria-label="Create listing"]'),
        'createNewListingSingleButton' => env('FB_MARKETING_SELECTORS_LISTINGS_NEW_BUTTON_SINGLE', 'a[href="/marketplace/create/"]'),
        'createNewListingButton' => env('FB_MARKETING_SELECTORS_LISTINGS_NEW_BUTTON', 'a[aria-label="Create new listing"]'),
        'createNewVehicleListingButton' => env('FB_MARKETING_SELECTORS_LISTINGS_NEW_VEHICLE_BUTTON', 'a[href="/marketplace/create/vehicle/"]'),
        'detectMissingMobileNumber' => env('FB_MARKETING_SELECTORS_DETECT_MOBILE', '.mobileMirrorHeading:contains("Add a Mobile Number")'),
        'detectRequestReviewButton' => env('FB_MARKETING_SELECTORS_DETECT_REQUEST_REVIEW_BUTTON', 'div[aria-label="Request Review"] > div'),
        'detectReviewingRequestText' => env('FB_MARKETING_SELECTORS_DETECT_REVIEWING_REQUEST', 'div > span:contains(\'We\\\'re Reviewing Your Request\')'),
        'detectReviewingFinalText' => env('FB_MARKETING_SELECTORS_DETECT_REVIEWING_FINAL', 'div > span:contains(\'You Can\\\'t Buy or Sell on Facebook\')'),
        'detectReviewDisagreedText' => env('FB_MARKETING_SELECTORS_DETECT_REVIEW_DISAGREED', 'div > span:contains(\'you disagreed with the decision\')'),

        // listingPageQs
        'detectBoostListing' => env('FB_MARKETING_SELECTORS_LISTINGS_BOOST_DETECT', 'div[aria-label="Boost your listing"][role="button"]'),
        'listingItemMoreMenu' => env('FB_MARKETING_SELECTORS_LISTINGS_MORE_BUTTON', 'div[aria-label="More"][role="button"]'),
        'listingItemMoreMenuFirst' => env('FB_MARKETING_SELECTORS_LISTINGS_MORE_BUTTON_FIRST', 'div[aria-label="More"][role="button"]'),
        'firstItemLink' => env('FB_MARKETING_SELECTORS_LISTINGS_FIRST_ITEM', 'div > a[role="menuitem"]:contains("View Listing")'),
        'gotItButton' => env('FB_MARKETING_SELECTORS_LISTINGS_GOTIT_BUTTON', 'div[aria-label="Got it"]'),
        'closeButton' => env('FB_MARKETING_SELECTORS_LISTINGS_CLOSE_BUTTON', 'div[aria-label="Close"]')
    ],

    // Posting
    'posting' => [
        // Common Selectors for Posting
        'common' => [
            'closeFormButton' => env('FB_MARKETING_SELECTORS_POSTING_CLOSE_BUTTON', '[aria-label="Close"]'),
            'leavePageButton' => env('FB_MARKETING_SELECTORS_POSTING_LEAVE_BUTTON', '[aria-label="Leave Page"]'),
            'detectLimitReached' => env('FB_MARKETING_SELECTORS_POSTING_LIMIT_REACHED', 'div > span > span > span:contains(\'Limit reached\')')
        ],

        // Vehicle Posting
        'vehicle' => [
            'vehicleTypeDropdown' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPE', '[aria-label="Vehicle type"]'),
            'vehicleTypeOptionsParent' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_MENU', 'div[role="menu"] span'),
            'vehicleTypeOptions' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_TYPES', 'div[role="option"] span'),
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

            'vehicleMakeText' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_MAKE', '[aria-label="Make"] input'),
            'vehicleMakeDropdown' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_MAKE_DROPDOWN', '[aria-label="Make"]'),
            'vehicleMakeDropdownList' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_MAKE_DROPDOWN_LIST', '[role="option"]'),
            'vehicleModelText' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_MODEL', '[aria-label="Model"] input'),
            'vehicleModelDropdown' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_MODEL_DROPDOWN', '[aria-label="Model"]'),
            'vehicleModelDropdownList' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_MODEL_DROPDOWN_LIST', '[role="option"]'),

            'vehiclePrice' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_PRICE', '[aria-label="Price"] input'),

            'vehicleMileage' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_MILEAGE', '[aria-label="Mileage"] input'),

            'vehicleExteriorColor' => explode(',', env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_EXTERIOR_COLOR', '[aria-label="Exterior color"],[aria-label="Exterior colour"]')),
            'vehicleExteriorColorList' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_EXTERIOR_COLOR_LIST', '[role="option"]'),

            'vehicleInteriorColor' => explode(',', env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_INTERIOR_COLOR', '[aria-label="Interior color"],[aria-label="Interior colour"]')),
            'vehicleInteriorColorList' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_INTERIOR_COLOR_LIST', '[role="option"]'),

            'vehicleFuelType' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_FUEL_TYPE', '[aria-label="Fuel type"]'),
            'vehicleFuelTypeList' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_FUEL_TYPE_LIST', '[role="option"]'),

            'vehicleDescription' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_DESCRIPTION', '[aria-label="Description"] textarea'),

            'vehicleLocationInput' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_LOCATION_INPUT', '[aria-label="Location"] input'),
            'vehicleSearchFirstOption' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_LOCATION_FIRST_OPTION', '[role="option"] span'),

            'vehicleNextDisabledButton' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_NEXT_DISABLED_BUTTON', '[aria-label="Next"][aria-disabled="true"]'),
            'vehicleNextButton' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_NEXT_BUTTON', '[aria-label="Next"]'),
            'vehiclePublishButton' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_PUBLISH_BUTTON', '[aria-label="Publish"]'),

            'vehiclePublishFailedDialog' => env('FB_MARKETING_SELECTORS_POSTING_VEHICLE_FAILED_DIALOG', '[role="dialog"]'),
        ],
    ]
];
