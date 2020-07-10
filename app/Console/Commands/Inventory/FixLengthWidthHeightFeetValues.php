<?php

namespace App\Console\Commands\Inventory;

use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use Illuminate\Console\Command;

/**
 * Class FixLengthWidthHeightFeetValues
 * @package App\Console\Commands\Inventory
 */
class FixLengthWidthHeightFeetValues extends Command
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
    protected $signature = "inventory:fix_length_width_height_feet_values";

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
        foreach (self::COLUMNS as $feetColumn => $minInchesLength) {
            $inchesColumn = sprintf(self::INCHES_TEMPLATE, $feetColumn);

            $inventory = $this->inventoryRepository->getAll([
                Repository::CONDITION_AND_WHERE => [
                    [$inchesColumn, '>', $minInchesLength],
                    [$feetColumn, '=', 0]
                ]
            ]);

            foreach ($inventory as $item) {
                $feetValue = round($item->{$inchesColumn} / 12, 2);

                $params = [
                    'inventory_id' => $item->inventory_id,
                    $feetColumn => $feetValue,
                ];

                $this->inventoryRepository->update($params);
            }
        }

        foreach (self::COLUMNS as $feetColumn => $minInchesLength) {
            $inchesColumn = sprintf(self::INCHES_TEMPLATE, $feetColumn);

            $inventory = $this->inventoryRepository->getAll([
                Repository::CONDITION_AND_WHERE => [
                    [$inchesColumn, '>', $minInchesLength],
                    [$feetColumn, '!=', 0],
                ],
                Repository::CONDITION_AND_WHERE_RAW => [
                    ["ROUND({$inchesColumn}/12, 2) != {$feetColumn}"]
                ]
            ]);

            foreach ($inventory as $item) {
                if (floor($item->{$inchesColumn}/12) !== $item->{$feetColumn}) {
                    continue;
                }

                $feetValue = round($item->{$inchesColumn} / 12, 2);

                $params = [
                    'inventory_id' => $item->inventory_id,
                    $feetColumn => $feetValue,
                ];

                $this->inventoryRepository->update($params);
            }
        }

        return true;
    }

}
