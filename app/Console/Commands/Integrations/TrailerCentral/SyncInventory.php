<?php

declare(strict_types=1);

namespace App\Console\Commands\Integrations\TrailerCentral;

use App\Services\Integrations\TrailerCentral\Console\Inventory\SyncServiceInterface;
use App\Services\LoggerServiceInterface;

class SyncInventory extends AbstractSyncCommand
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'sync:inventory';

    /** @var string The console command description. */
    protected $description = 'Sync the inventory history from the Trailer Central database';

    public function __construct(SyncServiceInterface $service, LoggerServiceInterface $logger)
    {
        parent::__construct($service, $logger);
    }
}
