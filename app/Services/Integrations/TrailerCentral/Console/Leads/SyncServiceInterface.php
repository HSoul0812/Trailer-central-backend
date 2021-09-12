<?php

declare(strict_types=1);

namespace App\Services\Integrations\TrailerCentral\Console\Leads;

use App\Services\Integrations\TrailerCentral\Console\SyncServiceInterface as AbstractSyncServiceInterface;

interface SyncServiceInterface extends AbstractSyncServiceInterface
{
    public const PROCESS_NAME = 'leads';
}
