<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Website\Tracking;

use App\Models\Website\Tracking\TrackingUnit;
use App\Repositories\Website\Tracking\TrackingUnitRepository;
use App\Repositories\Website\Tracking\TrackingUnitRepositoryInterface;
use Tests\database\seeds\Website\Tracking\TrackingUnitSeeder;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;

class TrackingUnitRepositoryTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;

    /**
     * @var TrackingUnitSeeder
     */
    private $seeder;

    /**
     * Test that SUT is properly bound by the application
     *
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    public function testRepositoryInterfaceIsBound(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(TrackingUnitRepository::class, $concreteRepository);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @typeOfTest IntegrationTestCase
     * @dataProvider validQueryParametersProvider
     *
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     * @covers SourceRepository::getAll
     */
    public function testGetAll(array $params, int $expectedTotal): void
    {
        // Given I have a collection of tracking units
        $this->seeder->seed();

        // When I call getAll
        // Then I got a list of tracking units
        /** @var Collection $units */
        $units = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params));

        // And That list should be Collection instance
        self::assertInstanceOf(Collection::class, $units);

        // And the total of records should be the expected
        self::assertSame($expectedTotal, $units->count());
    }


    /**
     * Test that SUT is updating correctly
     *
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers TrackingUnitRepository::update
     */
    public function testUpdate(): void {
        $this->seeder->seed();

        // Given I have a collection of created tracking
        $units = $this->seeder->units;

        // Get TrackingUnit
        $unit = $units[array_rand($units, 1)];

        // Tracking unit already exists
        self::assertSame(1, TrackingUnit::where(['tracking_unit_id' => $unit->tracking_unit_id])->count());

        // When I call update with valid parameters
        /** @var TrackingUnit $unit */
        $trackingUnit = $this->getConcreteRepository()->update([
            'id' => $unit->tracking_unit_id,
            'session_id' => $unit->session_id,
            'inventory_id' => $unit->inventory_id,
            'type' => $unit->type,
            'referrer' => $unit->referrer,
            'path' => $unit->path,
            'inquired' => 0
        ]);

        // Then I should get a class which is an instance of TrackingUnit
        self::assertInstanceOf(TrackingUnit::class, $trackingUnit);

        // Tracking unit should still exist after update
        self::assertSame(1, TrackingUnit::where(['tracking_unit_id' => $unit->tracking_unit_id])->count());
    }

    /**
     * Test that SUT is marking inventory unit as inquired correctly
     *
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers TrackingUnitRepository::markUnitInquired
     */
    public function testMarkUnitInquiredInventory(): void {
        $this->seeder->seed();

        // Get Inventory
        $inventory = $this->seeder->inventory[1];

        // Get Specific Unit That Must Be Chosen
        $unit = $this->seeder->units[5];

        // Tracking unit already exists...
        self::assertSame(1, TrackingUnit::where(['tracking_unit_id' => $unit->tracking_unit_id])->count());

        // But is NOT inquired...
        self::assertSame(0, $unit->inquired);

        // When I call update with valid parameters
        /** @var TrackingUnit $unit */
        $trackingUnit = $this->getConcreteRepository()->markUnitInquired(
            $unit->session_id,
            $inventory->inventory_id,
            'inventory'
        );

        // Then I should get a class which is an instance of TrackingUnit
        self::assertInstanceOf(TrackingUnit::class, $trackingUnit);

        // Tracking unit should exist with inquired = 1 after update
        self::assertSame(1, TrackingUnit::where(['tracking_unit_id' => $unit->tracking_unit_id, 'inquired' => 1])->count());
    }

    /**
     * Test that SUT is marking part unit as inquired correctly
     *
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers TrackingUnitRepository::markUnitInquired
     */
    public function testMarkUnitInquiredPart(): void {
        $this->seeder->seed();

        // Get Part
        $part = $this->seeder->parts[0];

        // Get Specific Unit That Must Be Chosen
        $unit = $this->seeder->units[7];

        // Tracking unit already exists...
        self::assertSame(1, TrackingUnit::where(['tracking_unit_id' => $unit->tracking_unit_id])->count());

        // But is NOT inquired...
        self::assertSame(0, $unit->inquired);

        // When I call update with valid parameters
        /** @var TrackingUnit $unit */
        $trackingUnit = $this->getConcreteRepository()->markUnitInquired(
            $unit->session_id,
            $part->id,
            'part'
        );

        // Then I should get a class which is an instance of TrackingUnit
        self::assertInstanceOf(TrackingUnit::class, $trackingUnit);

        // Tracking unit should exist with inquired = 1 after update
        self::assertSame(1, TrackingUnit::where(['tracking_unit_id' => $unit->tracking_unit_id, 'inquired' => 1])->count());
    }


    /**
     * Examples of parameters with expected total.
     *
     * @return array[]
     */
    public function validQueryParametersProvider(): array
    {
        $sessionIdLambda = static function (TrackingUnitSeeder $seeder): string {
            return $seeder->tracking->session_id;
        };

        $singleInventoryIdLambda = static function (TrackingUnitSeeder $seeder): int {
            return $seeder->inventory[2]->getKey();
        };

        $multiPartIdLambda = static function (TrackingUnitSeeder $seeder): int {
            return $seeder->parts[0]->getKey();
        };

        return [                                // array $parameters, int $expectedTotal
            'By dummy session\'s id' => [['session_id' => $sessionIdLambda], 9],
            'By dummy session\'s type part' => [['session_id' => $sessionIdLambda, 'type' => 'part'], 5],
            'By dummy session\'s type inventory' => [['session_id' => $sessionIdLambda, 'type' => 'inventory'], 4],
            'By dummy session returning single inventory' => [['session_id' => $sessionIdLambda, 'type' => 'inventory', 'inventory_id' => $singleInventoryIdLambda], 1],
            'By dummy session returning multiple parts' => [['session_id' => $sessionIdLambda, 'type' => 'part', 'inventory_id' => $multiPartIdLambda], 3],
        ];
    }


    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new TrackingUnitSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return TrackingUnitRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): TrackingUnitRepositoryInterface
    {
        return $this->app->make(TrackingUnitRepositoryInterface::class);
    }
}
