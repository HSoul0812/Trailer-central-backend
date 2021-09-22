<?php

/** @noinspection PhpDocSignatureIsNotCompleteInspection */

namespace Database\Seeders\Inventory;

use App\Models\Inventory\InventoryLog;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Database\Factories\Inventory\InventoryLogFactory;
use Database\Seeders\WithArtifacts;
use Illuminate\Database\Seeder;

/**
 * This seeder will be run under demand by the tests itself, so please do not add to the main DataSeeder.
 */
class AverageStockSeeder extends Seeder
{
    use WithArtifacts;

    /**
     * Seeds the inventory stock flow for 4 manufactures (2 brands per manufacturer), which 3 of them will keep
     * their inventories 5 days and KZ manufacturer will keep their stock 15 days.
     *
     * @throws \JsonException when the json cannot be parsed
     */
    public function run(): void
    {
        $factory = InventoryLog::factory();
        $allManufactures = $this->fromJson('inventory/manufactures-brands.json');
        $randomManufactures = $allManufactures->filter(fn (array $data): bool => $data['name'] !== 'Kz')->random(3);
        $kzManufacturer = $allManufactures->filter(fn (array $data): bool => $data['name'] === 'Kz')->first();

        $numberOfInventoriesPerBrand = [
            [1, 2],
            [2, 1],
            [1, 1],
            [2, 2],
        ];

        $counter = 0;

        $oddDay = function () use (&$counter) {
            return (++$counter % 2) !== 0;
        };

        /** @var Carbon[] $period */
        $period = CarbonPeriod::create('2021-01-01 12:00:00', '2021-09-20 12:00:00')->addFilter($oddDay); // 132 days

        foreach ($period as $day) {
            $_day = $day->toImmutable();

            foreach ($randomManufactures as $index => $manufacturer) {
                $this->seedByManufacturer(
                    $manufacturer,
                    $_day,
                    $factory,
                    5,
                    $numberOfInventoriesPerBrand[$index]
                );
            }

            $this->seedByManufacturer($kzManufacturer, $_day, $factory, 15, $numberOfInventoriesPerBrand[3]);
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
        int $daysInStock,
        array $inventoriesPerBrand
    ): void {
        foreach ($manufacturer['brands'] as $index => $brand) { // 2 brands
            /** @var InventoryLog[] $inventories */
            $inventories = $factory->count($inventoriesPerBrand[$index])->create([
                'brand'        => $brand,
                'manufacturer' => $manufacturer['name'],
                'price'        => $factory->faker->numberBetween(1000, 2000),
                'event'        => InventoryLog::EVENT_CREATED,
                'status'       => InventoryLog::STATUS_AVAILABLE,
                'created_at'   => $date->toDateTimeString(),
            ]);

            foreach ($inventories as $inventory) {
                // suddenly that inventory is sold some 'X' days after its creation
                $factory->create([
                        'trailercentral_id' => $inventory->trailercentral_id,
                        'event'             => $inventory->event,
                        'status'            => InventoryLog::STATUS_SOLD,
                        'vin'               => $inventory->vin,
                        'brand'             => $inventory->brand,
                        'manufacturer'      => $inventory->manufacturer,
                        'price'             => $inventory->price,
                        'created_at'        => $date->addDays($daysInStock)->toDateTimeString(),
                    ]
                );
            }
        }
    }
}
