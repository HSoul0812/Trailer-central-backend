<?php

namespace Database\Factories\WebsiteUser;

use App\Models\WebsiteUser\WebsiteUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebsiteUserFactory extends Factory
{
    protected $model = WebsiteUser::class;

    /**
     * Define the model's default state.
     *
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->email,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ];
    }
}
