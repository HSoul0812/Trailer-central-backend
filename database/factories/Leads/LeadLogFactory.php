<?php

/** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */

declare(strict_types=1);

namespace Database\Factories\Leads;

use App\Models\Leads\LeadLog;
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
class LeadLogFactory extends Factory
{
    use WithArtifacts;
    use WithGetter;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LeadLog::class;

    /**
     * Define the model's default state.
     *
     * @throws JsonException when cannot load the json file
     */
    public function definition(): array
    {
        $manufacturer = $this->getManufacturers()->random();

        return [
            'trailercentral_id' => $this->faker->randomDigitNotZero(),
            'vin' => Str::upper(Str::random('21')),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email_address' => $this->faker->email(),
            'manufacturer' => $manufacturer['name'],
            'brand' => $this->faker->randomElement($manufacturer['brands']),
            'meta' => [],
            'submitted_at' => $this->faker->dateTimeBetween('-8 months'),
            'created_at' => $this->faker->dateTimeBetween('-8 months'),
        ];
    }

    /**
     * @throws JsonException
     */
    public function getManufacturers(): Collection
    {
        return $this->fromJson('leads/manufactures-brands.json');
    }

    public function getFaker(): Generator
    {
        return $this->faker;
    }
}
