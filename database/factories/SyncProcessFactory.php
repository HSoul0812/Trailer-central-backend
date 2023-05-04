<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SyncProcess;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SyncProcessFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SyncProcess::class;

    /**
     * Define the model's default state.
     *
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    public function definition(): array
    {
        return [
            'name' => Str::random('10'),
            'status' => SyncProcess::STATUS_WORKING,
            'meta' => [],
            'created_at' => now(),
            'updated_at' => now(),
            'finished_at' => now(),
        ];
    }
}
