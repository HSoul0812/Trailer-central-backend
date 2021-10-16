<?php

declare(strict_types=1);

namespace App\Repositories;

interface TransactionalRepository
{
    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;
}
