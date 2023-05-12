<?php

declare(strict_types=1);

namespace Tests\Common;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     */
    protected bool $seed = true;

    protected Generator $faker;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        $this->faker = Factory::create();

        parent::__construct($name, $data, $dataName);
    }
}
