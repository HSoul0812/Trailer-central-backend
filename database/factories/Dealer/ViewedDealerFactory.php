<?php

namespace Database\Factories\Dealer;

use App\Models\Dealer\ViewedDealer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class ViewedDealerFactory extends Factory
{
    protected $model = ViewedDealer::class;

    public function definition(): array
    {
        return [
            'name' => $this->fakeUniqueDealerName(),
            'dealer_id' => $this->fakeUniqueDealerId(),
            'inventory_id' => $this->faker->randomNumber(),
        ];
    }

    private function fakeUniqueDealerId(): int
    {
        do {
            $fakeDealerId = $this->faker->randomNumber();

            $exists = ViewedDealer::where('dealer_id', $fakeDealerId)->exists();
        } while ($exists);

        return $fakeDealerId;
    }

    private function fakeUniqueDealerName(): \Illuminate\Support\Stringable
    {
        do {
            // We append the name with random string with 5 characters to add entropy
            // this way we make sure there will be less likely to duplicate
            $fakeName = Str::of($this->faker->company())->append(' - ' . Str::random(5));

            $exists = ViewedDealer::where('name', $fakeName)->exists();
        } while ($exists);

        return $fakeName;
    }
}
