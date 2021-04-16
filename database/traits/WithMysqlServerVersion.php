<?php

declare(strict_types=1);

namespace Database\traits;

use Illuminate\Support\Facades\DB;

trait WithMysqlServerVersion
{
    public function version(): string
    {
        $pdo = DB::connection()->getPdo();
        $version = $pdo->query('select version()')->fetchColumn();

        return mb_substr($version, 0, 6);
    }
}
