<?php

/** @noinspection PhpDocSignatureIsNotCompleteInspection */

declare(strict_types=1);

namespace Database\Seeders\Inventory;

use App\Models\Inventory\InventoryLog;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Database\Factories\Inventory\InventoryLogFactory;
use Database\Seeders\WithArtifacts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;

/**
 * This seeder will be run under demand by the tests itself, so please do not add to the main DataSeeder.
 */
class AveragePriceSeeder extends Seeder
{
    use WithArtifacts;

    /**
     * Seeds the inventory price flow for 4 manufactures (2 brands per manufacturer), which 3 of them will keep
     * their inventories 5 days and KZ manufacturer will keep their stock 15 days.
     *
     * @throws JsonException when the json cannot be parsed
     */
    public function run(): void
    {
        $factory = InventoryLog::factory();
        $allManufactures = $this->fromJson('inventory/manufactures-brands.json');
        $randomManufactures = $allManufactures->filter(fn (array $data): bool => $data['name'] !== 'Winnebago')->random(3);
        $specificManufacturer = $allManufactures->filter(fn (array $data): bool => $data['name'] === 'Winnebago')->first();

        $numberOfInventoriesPerBrand = [
            [1, 2],
            [2, 1],
            [1, 1],
            [2, 2],
        ];

        $priceOfInventoriesPerBrand = [
            [1000, 1200],
            [500, 600],
            [800, 900],
            [400, 800],
        ];

        $counter = 0;

        $oddDay = function () use (&$counter) {
            return (++$counter % 2) !== 0;
        };

        /** @var Carbon[] $period */
        $period = CarbonPeriod::create('2021-01-01 12:00:00', '2021-09-20 12:00:00')->addFilter($oddDay); // 132 days

        foreach ($period as $date) {
            $day = $date->toImmutable();

            foreach ($randomManufactures as $index => $manufacturer) {
                $this->seedByManufacturer(
                    $manufacturer,
                    $day,
                    $factory,
                    5,
                    $numberOfInventoriesPerBrand[$index],
                    $priceOfInventoriesPerBrand[$index]
                );
            }

            $this->seedByManufacturer(
                $specificManufacturer,
                $day,
                $factory,
                15,
                $numberOfInventoriesPerBrand[3],
                $priceOfInventoriesPerBrand[3]
            );

            DB::statement('REFRESH MATERIALIZED VIEW inventory_price_average_per_day');
            DB::statement('REFRESH MATERIALIZED VIEW inventory_price_average_per_week');
        }
    }

    /**
     * @param array{name:string, brands:array} $manufacturer
     * @param array{int, int} $inventoriesPerBrand
     */
    private function seedByManufacturer(
        array $manufacturer,
        CarbonImmutable $date,
        InventoryLogFactory $factory,
        int $daysWithSamePrice,
        array $inventoriesPerBrand,
        array $pricesPerBrand,
    ): void {
        foreach ($manufacturer['brands'] as $index => $brand) { // 2 brands
            /** @var InventoryLog[] $inventories */
            $inventories = $factory->count($inventoriesPerBrand[$index])->create([
                'brand' => $brand,
                'manufacturer' => $manufacturer['name'],
                'price' => $pricesPerBrand[$index],
                'event' => InventoryLog::EVENT_CREATED,
                'status' => InventoryLog::STATUS_AVAILABLE,
                'created_at' => $date->toDateTimeString(),
            ]);

            foreach ($inventories as $inventory) {
                // suddenly that inventory suffered a price down 'X' days after its creation
                $factory->create([
                    'trailercentral_id' => $inventory->trailercentral_id,
                    'event' => InventoryLog::EVENT_PRICE_CHANGED,
                    'status' => $inventory->status,
                    'vin' => $inventory->vin,
                    'brand' => $inventory->brand,
                    'manufacturer' => $inventory->manufacturer,
                    'price' => $inventory->price - 100,
                    'created_at' => $date->addDays($daysWithSamePrice)->toDateTimeString(),
                ]
                );
            }
        }
    }
}
