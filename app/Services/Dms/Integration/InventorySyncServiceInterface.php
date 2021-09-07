<?php

declare(strict_types=1);

namespace App\Services\Dms\Integration;

interface InventorySyncServiceInterface extends SyncServiceInterface
{
    public const PROCESS_NAME = 'inventory';
}
