<?php

namespace App\Console\Commands;

use App\Services\Inventory\InventoryServiceInterface;
use App\Services\LoggerServiceInterface;
use Illuminate\Console\Command;

class CheckInventorySubscriptionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-subs';

    /** @var string The console command description. */
    protected $description = 'Check expiry date of inventory subscriptions';

    public function __construct(private InventoryServiceInterface $inventoryService, private LoggerServiceInterface $logger)
    {
        parent::__construct();
    }

    public function handle() {
        $this->logger->info("[CheckInventorySubscriptionsCommand] starting $this->signature ...");
        $this->info('Checking inventory subscriptions');
        $this->inventoryService->checkSubscriptions();
        $this->info('Finished checking inventory subscriptions');

    }
}
