<?php

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
            'channels' => ['single', 'slack'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'permission' => 0664,
            'tap' => [\App\Logging\DailyLogWithUsername::class],
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
            'tap' => [\App\Logging\DailyLogWithUsername::class],
        ],

        'inquiry' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/inquiry.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'autoassign' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commands/auto-assign.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'scrapereplies' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commands/scrape-replies.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'textcampaign' => [
            'driver' => 'daily',
            'path' => storage_path('logs/commands/text-campaigns.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'auth' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/auth.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'google' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/google.log'),
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'facebook' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/facebook.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'leads' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/leads.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'emailbuilder' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/emailbuilder.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'texts' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/texts.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'marketplace' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/marketplace.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'dispatch-fb' => [
            'driver' => 'daily',
            'path' => storage_path('logs/dispatch/facebook.log'),
            'level' => 'debug',
            'days' => 7,
            'permission' => 0664,
        ],

        'azure' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/azure.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'tunnels' => [
            'driver' => 'daily',
            'path' => storage_path('logs/repositories/tunnels.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
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
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
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
            'tap' => [\App\Logging\DailyLogWithUsername::class],
        ],

        'showroom-imports' => [
            'path' => storage_path('logs/showroom-imports.log'),
            'permission' => 0664,
        ],
        'blog' => [
            'driver' => 'daily',
            'path' => storage_path('logs/services/blog.log'),
            'level' => 'debug',
            'days' => 3,
            'permission' => 0664,
        ],
    ],

];
