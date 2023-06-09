<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RecaptchaLogFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'score' => $this->faker->randomFloat(2, 0, 1),
            'user_agent' => $this->faker->userAgent(),
            'ip' => $this->faker->ipv4(),
            'action' => $this->faker->name(),
            'path' => $this->faker->url(),
        ];
    }
}
