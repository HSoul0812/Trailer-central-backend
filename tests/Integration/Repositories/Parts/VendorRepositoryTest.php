<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Parts;

use App\Models\Parts\Vendor;
use App\Repositories\Parts\VendorRepository;
use App\Repositories\Parts\VendorRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\database\seeds\Part\PartSeeder;
use Tests\TestCase;

class VendorRepositoryTest extends TestCase
{
    /**
     * @var PartSeeder
     */
    private $seeder;

    /**
     * Test that SUT is properly bound by the application
     *
     * @group DMS
     * @group DMS_PARTS
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForTheRepositoryInterfaceIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(VendorRepository::class, $concreteRepository);
    }

    /**
     * @dataProvider queryParametersAndSummariesForGetAllProvider
     *
     * @group DMS
     * @group DMS_PARTS
     *
     * @param  array  $params  list of query parameters
     * @param  int  $expectedTotal
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     * @note IntegrationTestCase
     */
    public function testGetAllIsPaginatingAndFilteringAsExpected(
        array $params
    ): void {
        /** @var LengthAwarePaginator $vendors */
        $vendors = $this->getConcreteRepository()->getAll($params);

        $expectedTotal = Vendor::query()->when(isset($params['dealer_id']), function ($query) use ($params) {
            return $query->where('dealer_id', $params['dealer_id']);
        })->count();

        $this->assertInstanceOf(LengthAwarePaginator::class, $vendors);
        $this->assertSame($expectedTotal, $vendors->total());
    }

    /**
     * Examples of parameters and expected total.
     *
     * @return array[]
     */
    public function queryParametersAndSummariesForGetAllProvider(): array
    {
        $this->setup();
        $this->seeder->seed();

        $dealerId = $this->seeder->getDealerId();
        $countOfVendors = Vendor::count();
        $countOfDealerVendors = Vendor::where('dealer_id', $dealerId)->count();

        return [                // array $parameters, int $expectedTotal
            'Without dealer'    => [[]],
            'With dealer'       => [['dealer_id' => $dealerId]],
        ];
    }

    /**
     * @return VendorRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): VendorRepositoryInterface
    {
        return $this->app->make(VendorRepositoryInterface::class);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new PartSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }
}
