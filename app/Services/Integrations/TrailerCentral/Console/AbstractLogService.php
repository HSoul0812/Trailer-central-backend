<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Console;

use Closure;
use Illuminate\Database\ConnectionInterface;
use PDO;

abstract class AbstractLogService
{
    protected Closure|PDO $pdo; // this is to force the developer to use only the PDO (for performance purpose)

    public function __construct(ConnectionInterface $connection)
    {
        $this->pdo = $connection->getPdo();
    }

    protected function quote(mixed $value): string
    {
        return $this->pdo->quote($value);
    }
}
