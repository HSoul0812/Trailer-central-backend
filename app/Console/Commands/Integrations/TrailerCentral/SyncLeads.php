<?php

declare(strict_types=1);

namespace App\Console\Commands\Integrations\TrailerCentral;

use App\Services\Integrations\TrailerCentral\Console\Leads\SyncServiceInterface;
use App\Services\LoggerServiceInterface;

class SyncLeads extends AbstractSyncCommand
{
    /** @var string The name and signature of the console command. */
    protected $signature = 'sync:leads';

    /** @var string The console command description. */
    protected $description = 'Sync the leads history from the Trailer Central database';

    public function __construct(SyncServiceInterface $service, LoggerServiceInterface $logger)
    {
        parent::__construct($service, $logger);
    }
}
