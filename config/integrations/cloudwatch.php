<?php

return [
    'enabled' => env('CLOUDWATCH_ENABLED', true),
    'group_name' => env('CLOUDWATCH_GROUP_NAME'),
    'stream_name' => env('CLOUDWATCH_STREAM_NAME', 'laravel.log'),
    'sdk' => [
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'version' => 'latest'
    ],
    'retention' => env('CLOUDWATCH_LOG_RETENTION', 7),
    'level' => env('CLOUDWATCH_LOG_LEVEL', 'error')
];
