<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Traits\WithGetter;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Support\Facades\Facade;
use Mockery;

/**
 * @property-read Generator $faker
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    use WithGetter;

    /**
     * @var Generator
     */
    private $faker;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker::create();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
        Facade::clearResolvedInstances();
    }
}
