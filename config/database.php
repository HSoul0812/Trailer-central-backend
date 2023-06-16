<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'read' => [
                'host' => [
                    // if no separate read write hosts are configured, then
                    // the same host will be used for both connectsion
                    env('DB_READ_HOST', env('DB_HOST', '127.0.0.1')),
                ],
            ],
            'write' => [
                'host' => [
                    env('DB_HOST', '127.0.0.1'),
                ],
            ],
            'sticky' => false,
            'url' => env('DATABASE_URL'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'backup_mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'read' => [
                'host' => [
                    // if no separate read write hosts are configured, then
                    // the same host will be used for both connection
                    env('DB_HOST', '127.0.0.1'),
                ],
            ],
            'write' => [
                'host' => [
                    env('DB_HOST', '127.0.0.1'),
                ],
            ],
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations_api',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_CACHE_URL', env('REDIS_URL')),
            'host' => env('REDIS_CACHE_HOST', env('REDIS_HOST','127.0.0.1')),
            'password' => env('REDIS_CACHE_PASSWORD', env('REDIS_PASSWORD', null)),
            'port' => env('REDIS_CACHE_PORT', env('REDIS_PORT', '6379')),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

        /**
         * This connection is aimed to isolate any inventory cache-related process, so we will be able
         * to safely flush
         */
        'sdk-search-cache' => [
            'url' => env('REDIS_CACHE_URL', env('REDIS_URL')),
            'host' => env('REDIS_CACHE_HOST', env('REDIS_HOST','127.0.0.1')),
            'password' => env('REDIS_CACHE_PASSWORD', env('REDIS_PASSWORD', null)),
            'port' => env('REDIS_CACHE_PORT', env('REDIS_PORT', '6379')),
            'database' => env('REDIS_SDK_SEARCH_CACHE_DB', '10'),
        ],

        /**
         * This connection is aimed to isolate any inventory cache-related process, so we will be able
         * to safely flush
         */
        'sdk-single-cache' => [
            'url' => env('REDIS_CACHE_URL', env('REDIS_URL')),
            'host' => env('REDIS_CACHE_HOST', env('REDIS_HOST','127.0.0.1')),
            'password' => env('REDIS_CACHE_PASSWORD', env('REDIS_PASSWORD', null)),
            'port' => env('REDIS_CACHE_PORT', env('REDIS_PORT', '6379')),
            'database' => env('REDIS_SDK_SINGLE_CACHE_DB', '11'),
        ],

        /**
         * This new connection is aimed to isolate any jobs-related process
         */
        'jobs' => [
            'url' => env('REDIS_JOBS_URL', env('REDIS_URL')),
            'host' => env('REDIS_JOBS_HOST', env('REDIS_HOST','127.0.0.1')),
            'password' => env('REDIS_JOBS_PASSWORD', env('REDIS_PASSWORD', null)),
            'port' => env('REDIS_JOBS_PORT', env('REDIS_PORT', '6379')),
            'database' => env('REDIS_JOBS_DB', env('REDIS_DB', '2')),
        ],

        /**
         * This new connection is aimed to isolate any Horizon process, specially its metadata and metrics
         */
        'horizon' => [
            'url' => env('REDIS_JOBS_URL', env('REDIS_URL')),
            'host' => env('REDIS_JOBS_HOST', env('REDIS_HOST','127.0.0.1')),
            'password' => env('REDIS_JOBS_PASSWORD', env('REDIS_PASSWORD', null)),
            'port' => env('REDIS_JOBS_PORT', env('REDIS_PORT', '6379')),
            'database' => env('REDIS_HORIZON_DB', env('REDIS_DB', '3')),
        ],

        /**
         * We need to investigate why we need separate connections for `dealer-proxy`, `dealer-tunnels`, `persist`
         */
        'dealer-proxy' => [
            'host' => env('REDIS_HOST_PROXY', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD_PROXY', null),
            'port' => env('REDIS_PORT_PROXY', '6379'),
            'database' => env('DEALER_PROXY_REDIS_DB', '0'),
            'options' => [
                'prefix' => ''
            ]
        ],

        'dealer-tunnels' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '0')
        ],

        'persist' => [
            'url' => env('REDIS_PERSIST_URL'),
            'host' => env('REDIS_PERSIST_HOST', env('REDIS_HOST', '127.0.0.1')),
            'password' => env('REDIS_PERSIST_PASSWORD', null),
            'port' => env('REDIS_PERSIST_PORT', '6379'),
            'database' => env('REDIS_PERSIST_CACHE_DB', '0'),
            'options' => [
                'prefix' => env('REDIS_PERSIST_PREFIX', '')
            ]
        ]
    ],
];
