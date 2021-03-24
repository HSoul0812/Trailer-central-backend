<?php

namespace App\Console\Commands\Inventory;

use App\Services\Inventory\InventoryServiceInterface;
use Illuminate\Console\Command;

/**
 * Class DeleteDuplicateItems
 * @package App\Console\Commands\Inventory
 */
class DeleteDuplicates extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "inventory:delete_duplicates {dealer_id}";

    /**
     * @var InventoryServiceInterface
     */
    private $inventoryService;

    /**
     * DeleteDuplicates constructor.
     * @param InventoryServiceInterface $inventoryService
     */
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
        $dealerId = $this->argument('dealer_id');

        $result = $this->inventoryService->deleteDuplicates($dealerId);

        foreach ($result as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $this->info($key . ': ' . $value);
        }

        return true;
    }
}
