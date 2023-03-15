<?php

namespace Database\Factories;

use App\Domains\UserTracking\Types\UserTrackingEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class UserTrackingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'visitor_id' => $this->faker->uuid(),
            'website_user_id' => null,
            'event' => UserTrackingEvent::PAGE_VIEW,
            'url' => $this->faker->url(),
            'meta' => [
                'foo' => 'bar',
            ],
        ];
    }
}
