<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Console;

use Closure;
use Illuminate\Support\Facades\DB;
use PDO;

abstract class AbstractLogService
{
    protected Closure|PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::connection()->getPdo();
    }

    protected function quote(mixed $value): string
    {
        return $this->pdo->quote($value);
    }
}
