<?php

namespace Database\Factories;

use App\Domains\UserTracking\Types\UserTrackingEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'event' => $this->faker->randomElement([
                UserTrackingEvent::PAGE_VIEW,
                UserTrackingEvent::IMPRESSION,
            ]),
            'url' => $this->faker->url(),
            'page_name' => $this->faker->word(),
            'meta' => [
                'foo' => 'bar',
            ],
            'ip_address' => $this->faker->ipv4(),
            'location_processed' => $this->faker->boolean(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'country' => $this->faker->countryCode(),
        ];
    }

    public function hasUSIpAddress(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'ip_address' => '194.59.12.191',
            ];
        });
    }

    public function locationUnprocessed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'location_processed' => false,
            ];
        });
    }

    public function locationProcessed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'location_processed' => true,
            ];
        });
    }

    public function noLocationData(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'city' => null,
                'state' => null,
                'country' => null,
            ];
        });
    }
}
