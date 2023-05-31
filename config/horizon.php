<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Horizon will be accessible from. If this
    | setting is null, Horizon will reside under the same domain as the
    | application. Otherwise, this value will serve as the subdomain.
    |
    */

    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Horizon will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => 'horizon',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection where Horizon will store the
    | meta information required for it to function. It includes the list
    | of supervisors, failed jobs, job metrics, and other information.
    |
    */

    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing all Horizon data in Redis. You
    | may modify the prefix when you are running multiple installations
    | of Horizon on the same server so that they don't have problems.
    |
    */

    'prefix' => env('HORIZON_PREFIX', 'horizon:'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Horizon route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when the LongWaitDetected event
    | will be fired. Every connection / queue combination may have its
    | own, unique threshold (in seconds) before this event is fired.
    |
    */

    'waits' => [
        'redis:default' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in minutes) you desire Horizon to
    | persist the recent and failed jobs. Typically, recent jobs are kept
    | for one hour while all failed jobs are stored for an entire week.
    |
    */

    'trim' => [
        'recent' => 60,
        'completed' => 1,
        'recent_failed' => 10080,
        'failed' => 1440,
        'monitored' => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Horizon's "terminate" command will not
    | wait on all of the workers to terminate unless the --wait option
    | is provided. Fast termination can shorten deployment delay by
    | allowing a new instance of Horizon to start while the last
    | instance will continue to terminate each of its workers.
    |
    */

    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Memory Limit (MB)
    |--------------------------------------------------------------------------
    |
    | This value describes the maximum amount of memory the Horizon worker
    | may consume before it is terminated and restarted. You should set
    | this value according to the resources available to your server.
    |
    */

    'memory_limit' => 64,

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker settings used by your application
    | in all environments. These supervisors and settings handle all your
    | queued jobs and will be provisioned by Horizon during deployment.
    |
    */

    // https://laravel.com/docs/6.x/horizon

    'environments' => [
        'local' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => [
                    'inventory',
                    'parts',
                    'parts-export-new',
                    'factory-feeds',
                    'cvr-send-file',
                    'reports',
                    'emailbuilder',
                    'blog-posts',
                    'scrapereplies',
                    'inquiry',
                    'fb-catalog',
                    //'fb-messenger',
                    'ecommerce',
                    'crm-users',
                    'manufacturers',
                    //'hot-potato'
                    'dealer-exports',
                ],
                'balance' => 'auto',
                'processes' => 1,
                'tries' => 3,
                'delay' => 3,
                'timeout' => 3600,
            ],
            'supervisor-2' => [
                'connection' => 'redis',
                'queue' => ['overlay-images', 'overlay-images-low', 'inventory-cache'],
                'balance' => 'simple',
                'processes' => 10,
                'tries' => 3,
                'timeout' => 3600,
            ],
            'supervisor-3' => [
                'connection' => 'redis',
                'queue' => ['scout'],
                'balance' => 'auto',
                'processes' => 5,
                'tries' => 3,
                'timeout' => 3600,
            ],
            'supervisor-4' => [
                'connection' => 'redis',
                'queue' => ['batched-jobs'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'tries' => 1,
                'timeout' => 21600,
            ]
        ],

        'dev' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => [
                    'inventory',
                    'parts',
                    'parts-export-new',
                    'factory-feeds',
                    'cvr-send-file',
                    'reports',
                    'emailbuilder',
                    'blog-posts',
                    'scrapereplies',
                    'inquiry',
                    'fb-catalog',
                    //'fb-messenger',
                    'ecommerce',
                    'crm-users',
                    'manufacturers',
                    //'hot-potato'
                    'dealer-exports',
                ],
                'balance' => 'auto',
                'processes' => 1,
                'tries' => 3,
                'delay' => 3,
                'timeout' => 3600,
            ],
            'supervisor-2' => [
                'connection' => 'redis',
                'queue' => ['inventory-cache'],
                'balance' => 'simple',
                'processes' => 5,
                'tries' => 3,
                'timeout' => 900,
            ],
            'supervisor-3' => [
                'connection' => 'redis',
                'queue' => ['overlay-images', 'overlay-images-low'],
                'balance' => 'auto',
                'minProcesses' => 4,
                'maxProcesses' => 30,
                'tries' => 3,
                'timeout' => 3600,
            ],
            'supervisor-4' => [
                'connection' => 'redis',
                'queue' => ['scout'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'tries' => 3,
                'timeout' => 3600,
            ],
            'supervisor-5' => [
                'connection' => 'redis',
                'queue' => ['batched-jobs'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'tries' => 1,
                'timeout' => 21600,
            ]
        ],

        'staging' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => [
                    'inventory',
                    'factory-feeds',
                    //'fb-messenger',
                    'inquiry',
                    'blog-posts',
                    'ecommerce',
                    'crm-users',
                    'manufacturers',
                    //'hot-potato'
                ],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 8,
                'tries' => 3,
                'timeout' => 60,
            ],
            'supervisor-2' => [
                'connection' => 'redis',
                'queue' => ['scout'],
                'minProcesses' => 3,
                'maxProcesses' => 24,
                'balance' => 'auto',
                'tries' => 3,
                'timeout' => 900,
            ],
            'supervisor-3' => [
                'connection' => 'redis',
                'queue' => ['fb-catalog'],
                'balance' => 'auto',
                'processes' => 1,
                'tries' => 3,
                'timeout' => 360,
            ],
            'supervisor-4' => [
                'connection' => 'redis',
                'queue' => ['cvr-send-file'],
                'balance' => false,
                'processes' => 1,
                'tries' => 3,
                'timeout' => 600,
            ],
            'supervisor-5' => [
                'connection' => 'redis',
                'queue' => ['reports', 'emailbuilder', 'dealer-exports'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 3,
                'tries' => 3,
                'timeout' => 3600,
            ],
            'supervisor-6' => [
                'connection' => 'redis',
                'queue' => ['parts', 'parts-export-new'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'tries' => 3,
                'timeout' => 7200,
            ],
            'supervisor-7' => [
                'connection' => 'redis',
                'queue' => ['scrapereplies'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'tries' => 1,
                'timeout' => 600,
            ],
            'supervisor-8' => [
                'connection' => 'redis',
                'queue' => ['batched-jobs'],
                'balance' => 'auto',
                'minProcesses' => 3,
                'maxProcesses' => 6,
                'tries' => 1,
                'timeout' => 21600,
            ],
            'supervisor-9' => [
                'connection' => 'redis',
                'queue' => ['overlay-images'],
                'balance' => 'auto',
                'minProcesses' => 5,
                'maxProcesses' => 10,
                'tries' => 3,
                'timeout' => 14400
            ],
            'supervisor-10' => [
                'connection' => 'redis',
                'queue' => ['overlay-images-low'],
                'balance' => 'auto',
                'minProcesses' => 5,
                'maxProcesses' => 20,
                'tries' => 3,
                'timeout' => 14400
            ],
            'supervisor-11' => [
                'connection' => 'redis',
                'queue' => ['inventory-cache'],
                'minProcesses' => 2,
                'maxProcesses' => 6,
                'balance' => 'auto',
                'tries' => 3,
                'timeout' => 900,
            ],
        ],

        'production' => [
            'supervisor-1' => [
                'connection' => 'redis',
                'queue' => [
                    'inventory',
                    'factory-feeds',
                    'fb-messenger',
                    'inquiry',
                    'blog-posts',
                    'ecommerce',
                    'crm-users',
                    'manufacturers',
                    //'hot-potato'
                ],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 8,
                'tries' => 3,
                'timeout' => 60,
            ],
            'supervisor-2' => [
                'connection' => 'redis',
                'queue' => ['inventory-cache'],
                'balance' => 'auto',
                'minProcesses' => 3,
                'maxProcesses' => 25,
                'tries' => 3,
                'timeout' => 900
            ],
            'supervisor-3' => [
                'connection' => 'redis',
                'queue' => ['scout'],
                'balance' => 'auto',
                'minProcesses' => 10,
                'maxProcesses' => 50,
                'tries' => 3,
                'timeout' => 900,
            ],
            'supervisor-4' => [
                'connection' => 'redis',
                'queue' => ['fb-catalog'],
                'balance' => 'auto',
                'processes' => 3,
                'tries' => 3,
                'timeout' => 360,
            ],
            'supervisor-5' => [
                'connection' => 'redis',
                'queue' => ['cvr-send-file'],
                'balance' => false,
                'processes' => 1,
                'tries' => 3,
                'timeout' => 600,
            ],
            'supervisor-6' => [
                'connection' => 'redis',
                'queue' => ['reports', 'emailbuilder', 'dealer-exports'],
                'balance' => 'auto',
                'minProcesses' => 3,
                'maxProcesses' => 30,
                'tries' => 3,
                'timeout' => 3600,
            ],
            'supervisor-7' => [
                'connection' => 'redis',
                'queue' => ['parts', 'parts-export-new'],
                'balance' => 'auto',
                'minProcesses' => 5,
                'maxProcesses' => 10,
                'tries' => 3,
                'timeout' => 7200,
            ],
             'supervisor-8' => [
                'connection' => 'redis',
                'queue' => ['scrapereplies'],
                'balance' => 'auto',
                'minProcesses' => 5,
                'maxProcesses' => 100,
                'tries' => 1,
                'timeout' => 600,
            ],
            'supervisor-9' => [
                'connection' => 'redis',
                'queue' => ['overlay-images'],
                'balance' => 'auto',
                'minProcesses' => 5,
                'maxProcesses' => 60,
                'tries' => 3,
                'timeout' => 14400
            ],
            'supervisor-10' => [
                'connection' => 'redis',
                'queue' => ['overlay-images-low'],
                'balance' => 'auto',
                'minProcesses' => 5,
                'maxProcesses' => 60,
                'tries' => 3,
                'timeout' => 14400
            ],
            'supervisor-11' => [
                'connection' => 'redis',
                'queue' => ['batched-jobs'],
                'balance' => 'auto',
                'minProcesses' => 3,
                'maxProcesses' => 30,
                'tries' => 1,
                'timeout' => 21600,
            ]
        ],
    ],
];
