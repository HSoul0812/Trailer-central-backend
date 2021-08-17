<?php

namespace App\Console\Commands\Inventory;

use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Console\Command;

class AutoArchiveSoldItems extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "inventory:auto-archive-sold-items";

    /**
     * @var InventoryServiceInterface
     */
    protected $inventoryService;

    public function __construct(InventoryServiceInterface $inventoryService)
    {
        parent::__construct();

        $this->inventoryService = $inventoryService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $result = $this->inventoryService->archiveSoldItems();

        $this->info('Archived item ids: ' . implode(',', $result));

        return true;
    }
}
