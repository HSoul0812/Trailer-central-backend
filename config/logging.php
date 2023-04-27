<?php

use App\Logging\CloudWatchPusher;
use App\Logging\DailyLogWithUsername;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'slack', 'sentry'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'permission' => 0664,
            'tap' => [
                DailyLogWithUsername::class
            ]
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 7,
            'permission' => 0664,
            'tap' => [
                DailyLogWithUsername::class,
                CloudWatchPusher::class
            ]
        ],

        'inquiry' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/inquiry.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'interaction' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/interaction.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'autoassign' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commands/auto-assign.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'hotpotato' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commands/hot-potato.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'scraperepliesjob' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commands/scrape-replies-job.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'scrapereplies' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commands/scrape-replies.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'textcampaign' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commands/text-campaigns.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'import' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commands/import.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'leads-export' => [
            'driver' => 'daily',
            'path' => storage_path('logs/jobs/leads-export.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [CloudWatchPusher::class]
        ],

        'inventory-overlays' => [
            'driver' => 'daily',
            'path' => storage_path('logs/jobs/inventory-overlays.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [CloudWatchPusher::class]
        ],

        'images' => [
            'driver' => 'daily',
            'path' => storage_path('logs/jobs/images.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
        ],

        'auth' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/auth.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'imap' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/imap.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'google' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/google.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 7,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'facebook' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/facebook.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'fb-catalog' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/fb-catalog.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'leads' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/leads.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'emailbuilder' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/emailbuilder.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'inventory' => [
            'driver' => 'daily',
            'path' => storage_path('logs/inventory.log'),
            'level' => env('INVENTORY_LOG_LEVEL', 'debug'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'texts' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/texts.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'marketplace' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/marketplace.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'dispatch-fb' => [
            'driver' => 'daily',
            'path' => storage_path('logs/dispatch/facebook.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 7,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'dispatch-cl' => [
            'driver' => 'daily',
            'path' => storage_path('logs/dispatch/craigslist.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 7,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'cl-client' => [
            'driver' => 'daily',
            'path' => storage_path('logs/repositories/cl-client.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'azure' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/azure.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'tunnels' => [
            'driver' => 'daily',
            'path' => storage_path('logs/repositories/tunnels.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'slack-cl' => [
            'driver' => 'slack',
            'url' => env('CLAPP_SLACK_WEBHOOK_URL', ''),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('CLAPP_SLACK_LEVEL', 'info'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'error'),
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'stdout' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stdout',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'error'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'error'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'driver' => 'daily',
            'path' => storage_path('logs/emergency.log'),
            'days' => 7,
            'permission' => 0664,
            'tap' => [
                DailyLogWithUsername::class,
                CloudWatchPusher::class
            ]
        ],

        'showroom-imports' => [
            'driver' => 'daily',
            'path' => storage_path('logs/showroom-imports.log'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],
        'blog' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/blog.log'),
            'level' => env('LOG_LEVEL', 'error'),
            'days' => 3,
            'permission' => 0664,
            'tap' => [
                CloudWatchPusher::class
            ]
        ],
        'sentry' => [
            'driver' => 'sentry',
            'level' => env('SENTRY_LOG_LEVEL', 'error')
        ]
    ],

    'dealer-export' => [
        'driver' => 'daily',
        'path' => storage_path('logs/commands/dealer-export.log'),
        'level' => env('LOG_LEVEL', 'error'),
        'days' => 3,
        'permission' => 0664,
        'tap' => [
            CloudWatchPusher::class
        ]
    ],

];
