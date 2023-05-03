<?php

/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */

declare(strict_types=1);

namespace Database\Factories\Inventory;

use App\Models\Inventory\InventoryLog;
use App\Support\Traits\WithGetter;
use Database\Seeders\WithArtifacts;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonException;

/**
 * @property Generator $faker
 */
class InventoryLogFactory extends Factory
{
    use WithArtifacts;
    use WithGetter;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InventoryLog::class;

    /**
     * Define the model's default state.
     *
     * @throws JsonException when cannot load the json file
     */
    public function definition(): array
    {
        $events = [InventoryLog::EVENT_CREATED, InventoryLog::EVENT_UPDATED, InventoryLog::EVENT_PRICE_CHANGED];

        $statuses = [InventoryLog::STATUS_AVAILABLE, InventoryLog::STATUS_SOLD];

        $manufacturer = $this->getManufacturers()->random();

        $inventory_id = Str::of(microtime(true) . $this->faker->randomDigitNotZero())
            ->replace('.', '')
            ->substr(-7);

        return [
            'trailercentral_id' => $inventory_id,
            'event' => $this->faker->randomElement($events),
            'status' => $this->faker->randomElement($statuses),
            'vin' => Str::upper(Str::random('21')),
            'manufacturer' => $manufacturer['name'],
            'brand' => $this->faker->randomElement($manufacturer['brands']),
            'price' => $this->faker->numberBetween(500, 1000),
            'meta' => [],
            'created_at' => $this->faker->dateTimeBetween('-8 months'),
        ];
    }

    /**
     * @throws JsonException
     */
    public function getManufacturers(): Collection
    {
        return $this->fromJson('inventory/manufactures-brands.json');
    }

    public function getFaker(): Generator
    {
        return $this->faker;
    }
}
