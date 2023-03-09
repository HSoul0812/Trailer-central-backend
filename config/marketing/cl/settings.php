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

    // Truncated Max Values
    'truncate' => [
        'title' => env('CLAPP_TRUNCATE_TITLE', '70'),
        'contact' => env('CLAPP_TRUNCATE_CONTACT', '31'),
        'maxBody' => env('CLAPP_TRUNCATE_BODY_MAX', '29995'),
        'make' => env('CLAPP_TRUNCATE_MAKE', '32'),
        'model' => env('CLAPP_TRUNCATE_MODEL', '32')
    ],

    // Costs
    'costs' => [
        'min' => env('CLAPP_COSTS_MIN', '7'),
        'fee' => env('CLAPP_COSTS_FEE', '0.025')
    ],

    // Set Daily Limits
    'limits' => [
        'inventories' => env('CLAPP_LIMIT_INVENTORIES', '10'),
        'updates' => env('CLAPP_LIMIT_UPDATES', '10'),
        'images' => env('CLAPP_LIMIT_IMAGES', '24')
    ],

    // Warning
    'warning' => [
        'enabled' => env('CLAPP_WARNING_ENABLED', '1'),
        'ignore' => env('CLAPP_WARNING_IGNORE', '112 113'),
        'elapse' => [
            'warning' => env('CLAPP_WARNING_ELAPSE_WARNING', '60'),
            'error' => env('CLAPP_WARNING_ELAPSE_ERROR', '120'),
            'critical' => env('CLAPP_WARNING_ELAPSE_CRITICAL', '180')
        ],
        'clients' => [
            'low' => env('CLAPP_WARNING_CLIENTS_LOW', '1'),
            'edit' => env('CLAPP_WARNING_CLIENTS_EDIT_LOW', '0')
        ],
        'overrides' => [
            'elapse' => [
                'warning' => env('CLAPP_WARNING_OVERRIDE_ELAPSE_WARNING'),
                'error' => env('CLAPP_WARNING_OVERRIDE_ELAPSE_ERROR'),
                'critical' => env('CLAPP_WARNING_OVERRIDE_ELAPSE_ERROR')
            ],
            'clients' => [
                'low' => env('CLAPP_WARNING_OVERRIDE_CLIENTS_LOW'),
                'edit' => env('CLAPP_WARNING_OVERRIDE_CLIENTS_EDIT_LOW')
            ],
        ]
    ],

    // Slack Warnings
    'slack' => [
        'webhook' => env('CLAPP_SLACK_WEBHOOK_URL'),
        'level' => env('CLAPP_SLACK_LEVEL', 'info'),
        'critical' => env('CLAPP_SLACK_CRITICAL_NOTIFY_USER'),
        'interval' => env('CLAPP_SLACK_MESSAGE_INTERVAL', '60')
    ],

    // Image Domain
    'images' => [
        'domain' => env('CLAPP_SETTINGS_IMAGE_DOMAIN', 'https://dealer-cdn.com')
    ],

    // Show on Website Exceptions
    'overrides' => [
        'showOnWebsite' => env('CLAPP_OVERRIDE_HIDDEN', '1147,8467,7206,7099,6181,5542,5540,5541,7439,5521,6461,7638,10232'),
        'statusAll' => env('CLAPP_OVERRIDE_STATUS_ALL', '5936,5840'),
        'statusOnOrder' => env('CLAPP_OVERRIDE_STATUS_ON_ORDER', '5251'),
    ],

    // Scheduler settings
    'scheduler' => [
        'allDay' => env('CLAPP_SCHEDULER_ALL_DAY', false)
    ]
];
