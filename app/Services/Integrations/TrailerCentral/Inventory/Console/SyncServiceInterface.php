<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Inventory\Console;

use App\Services\SyncServiceInterface as AbstractSyncServiceInterface;

interface SyncServiceInterface extends AbstractSyncServiceInterface
{
    public const PROCESS_NAME = 'inventory';
}
