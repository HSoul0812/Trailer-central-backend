<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Inventory\Floorplan;

use App\Models\Parts\Vendor;
use App\Repositories\Inventory\Floorplan\VendorRepository;
use App\Repositories\Inventory\Floorplan\VendorRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 */
class VendorRepositoryTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_INVENTORY_FLOORPLAN
     *
     * @note IntegrationTestCase
     */
    public function testGetAllIsPaginatingAndFilteringAsExpected(): void
    {
        $fakeVendors = $this->getFakeVendors();

        $vendorRepository = resolve(VendorRepositoryInterface::class);

        $this->assertInstanceOf(VendorRepository::class, $vendorRepository);

        $this->getTestParams()->each(function(array $param) use ($vendorRepository){
            // /** @var LengthAwarePaginator $vendors */
            $vendors = $vendorRepository->getAll($param[0]);

            $this->assertInstanceOf(LengthAwarePaginator::class, $vendors);
            $this->assertTrue($vendors->contains('name', $param[1]));
            $this->assertFalse($vendors->contains('name', $param[2]));
        });

        $fakeVendors->each(function (Vendor $vendor) {
            $vendor->forceDelete();
        });
    }

    /**
     * Get the fake vendors
     *
     * @return Collection
     */
    private function getFakeVendors(): Collection
    {
        $fakeVendors = collect([]);

        $fakeVendors->push(factory(Vendor::class)->create([
            'name' => 'Non Dealer Vendor Unit Test',
            'show_on_floorplan' => 1,
        ]));

        $fakeVendors->push(factory(Vendor::class)->create([
            'dealer_id' => 1001,
            'name' => 'Dealer 1001 Vendor Unit Test',
            'show_on_floorplan' => 1,
        ]));

        return $fakeVendors;
    }

    /**
     * Get test params
     *
     * @return Collection
     */
    private function getTestParams(): Collection
    {
        return collect([
            [['show_on_floorplan' => 1, 'search_term' => 'Non Dealer Vendor Unit Test'], 'Non Dealer Vendor Unit Test', 'Dealer 1001 Vendor Unit Test'],
            [['show_on_floorplan' => 1, 'dealer_id' => 1001, 'search_term' => 'Dealer 1001 Vendor Unit Test'], 'Dealer 1001 Vendor Unit Test', 'Non Dealer Vendor Unit Test'],
        ]);
    }
}
