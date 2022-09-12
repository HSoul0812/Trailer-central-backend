<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'local_tmp' => [
            'driver' => 'local',
            'root' => storage_path('app/public/tmp'),
            'url' => env('APP_URL') . '/storage/tmp',
            'visibility' => 'public',
        ],

        'test_resources' => [
            'driver' => 'local',
            'root' => base_path('tests/resources'),
        ],

        'qz_tray' => [
            'driver' => 'local',
            'root' => storage_path('qz-tray'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'visibility' => env('AWS_ACL', 'private'),
        ],

        's3email' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('MAIL_BUCKET'),
            'url' => env('AWS_URL'),
            'visibility' => env('AWS_ACL', 'private'),
        ],

        'ses' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('MAIL_BUCKET'),
            'url' => env('AWS_URL'),
            'visibility' => 'public'
        ],

        // use the same creds as default s3, use this separate config to make it easier to separate
        'partsCsvExports' => [
//            'driver' => 'local',
//            'root' => storage_path('app/public'),
//            'url' => env('APP_URL').'/storage',
//            'visibility' => 'public',

            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],

        'marketplaceExports' => [
            'driver' => 'local',
            'root' => storage_path('app/public/tmp/exports/marketplace'),
            'url' => env('APP_URL').'/storage/tmp/exports/marketplace',
            'visibility' => 'public',
        ],

        'tmp' => [
            'driver' => 'local',
            'root' =>  env('APP_TMP_DIR', '/tmp')
        ],
    ],
];
