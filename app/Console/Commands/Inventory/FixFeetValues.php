<?php

namespace App\Console\Commands\Inventory;

use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use Illuminate\Console\Command;

/**
 * Class FixFeetValues
 * @package App\Console\Commands\Inventory
 */
class FixFeetValues extends Command
{
    private const COLUMNS = [
        'length' => 90,
        'width' => 12,
        'height' => 20,
    ];

    private const INCHES_TEMPLATE = '%s_inches';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "inventory:fix_feet {dealer_id} {column}";

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;

    public function __construct(InventoryRepositoryInterface $inventoryRepo)
    {
        parent::__construct();

        $this->inventoryRepository = $inventoryRepo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $column = $this->argument('column');
        $dealerId = $this->argument('dealer_id');

        if (!in_array($column, array_keys(self::COLUMNS))) {
            throw new \InvalidArgumentException('Wrong column');
        }

        if (empty($dealerId)) {
            throw new \InvalidArgumentException('Wrong dealer_id');
        }

        $feetUpdates = 0;

        $this->info("Fixing {$column} feet");

        $inchesColumn = sprintf(self::INCHES_TEMPLATE, $column);

        $inventory = $this->inventoryRepository->getAll([
            Repository::CONDITION_AND_WHERE => [
                [$inchesColumn, '>', self::COLUMNS[$column]],
                ['dealer_id', '=' , $dealerId]
            ],
            Repository::CONDITION_AND_WHERE_RAW => [
                ["ROUND({$inchesColumn}/12, 2) != {$column}"]
            ]
        ]);

        foreach ($inventory as $item) {
            $feetValue = round($item->{$inchesColumn} / 12, 2);

            $params = [
                'inventory_id' => $item->inventory_id,
                $column => $feetValue,
            ];

            $feetUpdates++;

            $this->info("Updating feet: ".json_encode($params));
            $this->inventoryRepository->update($params);
        }

        $this->info("Fixed: {$feetUpdates}");

        return true;
    }
}
