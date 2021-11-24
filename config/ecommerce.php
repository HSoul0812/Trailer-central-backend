<?php

return [
    'allowed_websites' => [
        'auth' => [
            'password' => env('ALLOWED_SITE_PASSWORD', 'dGVzdFBhc3N3b3Jk')
        ],
    ],
    'textrail' => [
        'bearer' => env('TEXTRAIL_BEARER', '0m5elzlp6wp7pofevd0jt2i5w6d038mk'),
        'store' => env('TEXTRAIL_STORE', 'trailer_central_t1_sv'),
        'store_id' => env('TEXTRAIL_STORE_ID', 10),
        'api_url' => env('TEXTRAIL_API_URL', 'https://mcstaging.textrail.com/'),
        'is_guest_checkout' => env('TEXTRAIL_GUEST_CHECKOUT', true),
        'queue' => env('TEXTRAIL_QUEUE', 'ecommerce'),
        'payment_method' => env('TEXTRAIL_PAYMENT_METHOD', 'purchaseorder'),
        'return' =>[
            'default_status' => env('TEXTRAIL_RETURN_DEFAULT_STATUS', 'Pending'),
            'item_default_status' => env('TEXTRAIL_RETURN_ITEM_DEFAULT_STATUS', 'pending'),
            'item_default_reason' => (string) env('TEXTRAIL_RETURN_ITEM_DEFAULT_REASON', '204872'),
            'item_default_condition' => (string) env('TEXTRAIL_RETURN_ITEM_DEFAULT_CONDITION', '19'),
            'item_default_resolution' => (string) env('TEXTRAIL_RETURN_ITEM_DEFAULT_RESOLUTION', '13'),
        ]
    ]
];
