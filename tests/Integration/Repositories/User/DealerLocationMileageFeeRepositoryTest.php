<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\User;

use App\Models\Inventory\Category;
use App\Models\User\DealerLocation;
use App\Repositories\User\DealerLocationMileageFeeRepository;
use App\Repositories\User\DealerLocationMileageFeeRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\Inventory\CategorySeeder;
use Tests\database\seeds\User\DealerLocationMileageFeeSeeder;
use Tests\TestCase;

class DealerLocationMileageFeeRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    const DEFAULT_DEALER_ID = 1001;

    /**
     * Test that SUT is properly bound by the application
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION_MILEAGE_FEE
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForTheRepositoryInterfaceIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(DealerLocationMileageFeeRepository::class, $concreteRepository);
    }

    /**
     * @covers ::getAll
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION_MILEAGE_FEE
     *
     * @throws BindingResolutionException
     */
    public function testGetAllIsWorkingProperly(): void
    {
        $this->seeder->seed(5);

        $repository = $this->getConcreteRepository();

        $allMileageFees = $repository->getAll([
            'dealer_location_id' => $this->seeder->dealerLocation()->dealer_location_id
        ]);

        self::assertCount(5, $allMileageFees->toArray());

        $this->seeder->cleanUp();
    }

    /**
     * @covers ::bulkCreate
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION_MILEAGE_FEE
     *
     * @throws BindingResolutionException
     */
    public function testBulkCreateIsWorkingProperly(): void
    {
        $repository = $this->getConcreteRepository();

        $inventoryCategorySeeder = new CategorySeeder([]);
        $inventoryCategorySeeder->seed(5);

        $location = factory(DealerLocation::class)->create([
            'dealer_id' => self::DEFAULT_DEALER_ID
        ]);

        $allMileageFees = $repository->bulkCreate([
            'fee_per_mile' => 99,
            'dealer_location_id' => $location->dealer_location_id
        ]);

        self::assertCount(Category::count(), $allMileageFees);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new DealerLocationMileageFeeSeeder([]);
    }

    /**
     * @return DealerLocationMileageFeeRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): DealerLocationMileageFeeRepositoryInterface
    {
        return $this->app->make(DealerLocationMileageFeeRepositoryInterface::class);
    }
}
