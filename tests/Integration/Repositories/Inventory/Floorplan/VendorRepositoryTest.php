<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Inventory\Floorplan;

use App\Models\Inventory\Floorplan\Vendor;
use App\Repositories\Inventory\Floorplan\VendorRepository;
use App\Repositories\Inventory\Floorplan\VendorRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class VendorRepositoryTest extends TestCase
{
    /**
     * Test that SUT is properly bound by the application
     *
     * @group DMS
     * @group DMS_INVENTORY_FLOORPLAN
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
     * @dataProvider queryParametersAndSummariesForShowOnFloorPlanProvider
     *
     * @group DMS
     * @group DMS_INVENTORY_FLOORPLAN
     *
     * @param  array  $params  list of query parameters
     * @param  int  $expectedTotal
     * @param  int  $expectedLastPage
     * @param  string|null  $expectedName
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     * @note IntegrationTestCase
     */
    public function testGetAllIsPaginatingAndFilteringAsExpected(
        array $params,
        int $expectedTotal,
        int $expectedLastPage,
        ?string $expectedName
    ): void {
        /** @var LengthAwarePaginator $vendors */
        $vendors = $this->getConcreteRepository()->getAll($params);

        /** @var Vendor $firstRecord */
        $firstRecord = $vendors->first();

        self::assertInstanceOf(LengthAwarePaginator::class, $vendors);
        self::assertSame($expectedTotal, $vendors->total());
        self::assertSame($expectedLastPage, $vendors->lastPage());
        self::assertSame($firstRecord ? $firstRecord->name: $firstRecord, $expectedName);
    }

    /**
     * Examples of parameters, expected total and last page numbers, and first category label.
     *
     * @return array[]
     */
    public function queryParametersAndSummariesForShowOnFloorPlanProvider(): array
    {
        return [                                          // array $parameters, int $expectedTotal, int $expectedLastPage, string $expectedName
            'No parameters'                               => [['show_on_floorplan' => 1], 3, 1, 'Stonegate Industries'],
            'None dealer and filtered with matches'       => [['show_on_floorplan' => 1, 'search_term' => 'to remove'], 1, 1, 'Test Vendor To Remove'],
            'Dummy test dealer and filtered with matches' => [['show_on_floorplan' => 1, 'dealer_id' => 1001, 'search_term' => 'curb'], 1, 1, 'Curbed'],
            'None dealer filtered with no matches'        => [['show_on_floorplan' => 1, 'search_term' => 'acme'], 0, 1, null],
            'Dummy test dealer and paged by 3'            => [['show_on_floorplan' => 1, 'dealer_id' => 1001, 'per_page' => 3], 6, 2, 'Big Tex Trailers']
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
}
