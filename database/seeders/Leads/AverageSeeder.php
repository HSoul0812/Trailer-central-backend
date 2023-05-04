<?php

/** @noinspection PhpDocSignatureIsNotCompleteInspection */

declare(strict_types=1);

namespace Database\Seeders\Leads;

use App\Models\Leads\LeadLog;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Database\Factories\Leads\LeadLogFactory;
use Database\Seeders\WithArtifacts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;

class AverageSeeder extends Seeder
{
    use WithArtifacts;

    /**
     * @throws JsonException when the json cannot be parsed
     */
    public function run(): void
    {
        $factory = LeadLog::factory();
        $allManufactures = $this->fromJson('leads/manufactures-brands.json');
        $randomManufactures = $allManufactures->filter(fn (array $data): bool => $data['name'] !== 'Starcraft')->random(3);
        $specificManufacturer = $allManufactures->filter(fn (array $data): bool => $data['name'] === 'Starcraft')->first();

        $numberOfLeadsPerBrand = [
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

        foreach ($period as $date) {
            $day = $date->toImmutable();

            foreach ($randomManufactures as $index => $manufacturer) {
                $this->seedByManufacturer($manufacturer, $day, $factory, $numberOfLeadsPerBrand[$index]);
            }

            $this->seedByManufacturer($specificManufacturer, $day, $factory, $numberOfLeadsPerBrand[3]);

            DB::statement('REFRESH MATERIALIZED VIEW leads_average_per_day');
            DB::statement('REFRESH MATERIALIZED VIEW leads_average_per_week');
        }
    }

    /**
     * @param array{name:string, brands:array} $manufacturer
     * @param  array{int, int}  $inventoriesPerBrand
     */
    private function seedByManufacturer(
        array $manufacturer,
        CarbonImmutable $date,
        LeadLogFactory $factory,
        array $inventoriesPerBrand
    ): void {
        foreach ($manufacturer['brands'] as $index => $brand) { // 2 brands
            /* @var LeadLog[] $leads */
            $factory->count($inventoriesPerBrand[$index])->create([
                'brand' => $brand,
                'manufacturer' => $manufacturer['name'],
                'created_at' => $date->toDateTimeString(),
                'submitted_at' => $date->toDateTimeString(),
            ]);
        }
    }
}
