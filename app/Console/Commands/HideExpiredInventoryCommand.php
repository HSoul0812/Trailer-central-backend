<?php

namespace App\Console\Commands;

use App\Services\Inventory\InventoryServiceInterface;
use App\Services\LoggerServiceInterface;
use Illuminate\Console\Command;

class HideExpiredInventoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:hide-expired';

    /** @var string The console command description. */
    protected $description = 'Check expiry date of inventory subscriptions';

    public function __construct(private InventoryServiceInterface $inventoryService, private LoggerServiceInterface $logger)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->logger->info("[HideExpiredInventoryCommand] starting $this->signature ...");
        $this->info('Checking inventory expiry');
        $this->inventoryService->hideExpired();
        $this->info('Finished checking inventory expiry');
    }
}
