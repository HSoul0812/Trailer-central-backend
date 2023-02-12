<?php

namespace App\Console\Commands\Sync;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use PDO;

abstract class AbstractSyncFromRemoteCommand extends Command
{
    final public function handle(): void
    {
        $host = $this->argument('host');
        $dbName = $this->argument('db');
        $user = $this->argument('user');
        $port = $this->argument('port');
        $password = $this->getOutput()->askHidden('password'); // comment me when coding
        //$password = 'hardcode password to boost coding phase';

        Config::set('database.connections.remote', [
            'driver' => 'mysql',
            'read' => [
                'host' => [$host]
            ],
            'write' => [
                'host' => [$host]
            ],
            'sticky' => false,
            'port' => $port,
            'database' => $dbName,
            'username' => $user,
            'password' => $password,
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => env('DB_STRICT_MODE', false),
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);

        $this->sync();
    }

    abstract protected function sync(): void;
}
