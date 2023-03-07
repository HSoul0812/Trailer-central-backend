<?php

namespace Tests\Feature\User\DealerLocation;

use App\Models\Inventory\Category;
use App\Models\User\DealerLocation;
use App\Models\User\DealerLocationMileageFee;
use Tests\TestCase;
use Tests\database\seeds\Inventory\CategorySeeder;

class DealerLocationMileageFeeTest extends TestCase
{
    private $location;
    private $categories;
    private $inventoryCategoriesSeeder;

    const DEFAULT_DEALER_ID = 1001;

    public function setUp(): void
    {
        parent::setUp();

        $this->inventoryCategoriesSeeder = new CategorySeeder([]);

        $this->createDealerLocation();
        $this->createInventoryCategories();
    }

    protected function tearDown(): void
    {
        $this->tearDownSeed();

        parent::tearDown();
    }

    protected function tearDownSeed(): void
    {
        $this->location->mileageFees()->delete();
        $this->location->delete();
        $this->inventoryCategoriesSeeder->cleanUp();
    }

    protected function createDealerLocation()
    {
        $this->location = factory(DealerLocation::class)->create([
            'dealer_id' => self::DEFAULT_DEALER_ID
        ]);
    }

    protected function createInventoryCategories()
    {
        $this->inventoryCategoriesSeeder->seed(10);
        $this->categories = $this->inventoryCategoriesSeeder->data();
    }

    /**
     * @group DMS
     * @group DMS_DEALER_LOCATION_MILEAGE_FEE
     *
     * @return void
     */
    public function testBulkCreateWithouthAccessToken()
    {
        $response = $this->post("/api/user/dealer-location/{$this->location->dealer_location_id}/mileage-fee/all");

        $response->assertStatus(403);
    }

    /**
     * @group DMS
     * @group DMS_DEALER_LOCATION_MILEAGE_FEE
     *
     * @return void
     */
    public function testBulkCreateMileageFees()
    {
        $numberOfInventoryCategories = Category::count();

        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->post("/api/user/dealer-location/{$this->location->dealer_location_id}/mileage-fee/all", [
                'fee_per_mile' => 199.8
            ]);
        $response->assertStatus(200);

        $this->assertSame($numberOfInventoryCategories, DealerLocationMileageFee::where('dealer_location_id', $this->location->dealer_location_id)->count());

        $data = json_decode($response->getContent(), true);

        $this->assertCount($numberOfInventoryCategories, $data['data']);
    }

    /**
     * @group DMS
     * @group DMS_DEALER_LOCATION_MILEAGE_FEE
     *
     * @return void
     */
    public function testBulkCreateRequestValidation()
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->post("/api/user/dealer-location/{$this->location->dealer_location_id}/mileage-fee/all", []);

        $response->assertStatus(422);
        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('fee_per_mile', $json['errors']);
    }
}
