<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Parts;

use App\Models\Parts\Vendor;
use App\Repositories\Parts\VendorRepository;
use App\Repositories\Parts\VendorRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class VendorRepositoryTest extends TestCase
{
    /**
     * Test that SUT is properly bound by the application
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
     * @param  array  $params  list of query parameters
     * @param  int  $expectedTotal
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     * @note IntegrationTestCase
     */
    public function testGetAllIsPaginatingAndFilteringAsExpected(
        array $params,
        int $expectedTotal
    ): void {
        /** @var LengthAwarePaginator $vendors */
        $vendors = $this->getConcreteRepository()->getAll($params);

        self::assertInstanceOf(LengthAwarePaginator::class, $vendors);
        self::assertSame($expectedTotal, $vendors->total());
    }

    /**
     * Examples of parameters and expected total.
     *
     * @return array[]
     */
    public function queryParametersAndSummariesForGetAllProvider(): array
    {
        $countOfVendors = Vendor::count();

        return [                // array $parameters, int $expectedTotal
            'Without dealer'    => [[], $countOfVendors],
            'With dealer'       => [['dealer_id' => 1001, 'name' => 'Big Tex'], 1],
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
