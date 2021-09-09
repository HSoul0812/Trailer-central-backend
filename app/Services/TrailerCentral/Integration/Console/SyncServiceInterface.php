<?php

declare(strict_types=1);

namespace App\Services\TrailerCentral\Integration\Console;

interface SyncServiceInterface
{
    public function sync(): int;
}
