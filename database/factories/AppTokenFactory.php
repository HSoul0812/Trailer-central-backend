<?php

namespace Database\Factories;

use App\Models\AppToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class AppTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'app_name' => $this->faker->name(),
            'token' => Str::random(AppToken::TOKEN_LENGTH),
        ];
    }
}
