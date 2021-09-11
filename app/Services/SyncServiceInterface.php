<?php

declare(strict_types=1);

namespace App\Services;

interface SyncServiceInterface
{
    public function sync(): int;
}
