<?php

declare(strict_types=1);

namespace App\Services\TrailerCentral\Integration\Console;

interface InventorySyncServiceInterface extends SyncServiceInterface
{
    public const PROCESS_NAME = 'inventory';
}
