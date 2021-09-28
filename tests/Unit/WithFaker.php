<?php

declare(strict_types=1);

namespace Tests\Unit;

use Faker\Factory;
use Faker\Generator;

trait WithFaker
{
    /**
     * The Faker instance.
     */
    protected Generator $faker;

    /**
     * Setup up the Faker instance.
     */
    protected function setUpFaker(): void
    {
        $this->faker = $this->makeFaker();
    }

    /**
     * Get the default Faker instance for a given locale.
     */
    protected function faker(?string $locale = null): Generator
    {
        return is_null($locale) ? $this->faker : $this->makeFaker($locale);
    }

    /**
     * Create a Faker instance for the given locale.
     */
    protected function makeFaker(?string $locale = null): Generator
    {
        if (!isset($this->faker)) {
            $this->faker = Factory::create($locale ?? env('FAKER_LOCALE', Factory::DEFAULT_LOCALE));
        }

        return $this->faker;
    }
}
