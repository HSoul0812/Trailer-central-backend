<?php

namespace Tests\Feature\Parts;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrder;
use App\Models\Parts\Part;
use Tests\database\seeds\Part\PartSeeder;
use Tests\TestCase;

/**
 * Tests that SUT response with the desired information
 *
 * @package Tests\Feature\Parts
 *
 * @todo add create, update, delete tests
 */
class PartsFeatureTest extends TestCase
{
    /** @var PartSeeder */
    private $seeder;

    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new PartSeeder(['count' => 5, 'with' => ['purchaseOrders']]);
        $this->seeder->seed();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testIndexHttpOk(): void
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/parts');

        $response->assertOk();
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testSearchHasDataProperty(): void
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/parts');

        $response->assertJsonStructure([
            'data'
        ]);
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testPartHasNotPurchaseOrdesPropertyWhenIncludeParamIsNotPresent(): void
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/parts');

        $response->assertJsonMissing([
            'purchaseOrders'
        ]);
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testPartHasPurchaseOrdersPropertyAndItsStructureIsWellFormed(): void
    {
        $params = [
            'include' => 'purchaseOrders'
        ];

        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/parts?' . http_build_query($params));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'purchaseOrders' => [
                        'data',
                        'meta' => [
                            'has_not_completed'
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testPartHasPurchaseOrdersNotCompleted(): void
    {
        $seeder = new PartSeeder(['count' => 5, 'with' => ['purchaseOrders']]);
        $seeder->seed();

        $part = Part::query()
            ->whereHas('purchaseOrders.purchaseOrder', function ($query) {
                $query->where('dms_purchase_order.status', '!=', PurchaseOrder::STATUS_COMPLETED);
            })
            ->orderByDesc('id')
            ->first();

        $params = [
            'dealer_id' => $part->dealer_id,
            'per_page' => 1,
            'page' => 1,
            'query' => $part->sku,
            'include' => 'purchaseOrders'
        ];

        // When I search using the stock number
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/parts/search?' . http_build_query($params));

        $response
            ->assertSuccessful()
            ->assertJsonPath('data.0.purchaseOrders.meta.has_not_completed', true);
    }

    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testPartSearchWithDifferentDealerId(): void
    {
        $dealerId = $this->getTestDealerId();

        $params = [
            'dealer_id' => 5001,    // Passing different DealerId than Authenticated
            'per_page' => 10,
            'page' => 1,
        ];

        // When I search using the different dealer id
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/parts/search?' . http_build_query($params));

        $response
            ->assertSuccessful()
            ->assertJsonPath('data.0.dealer_id', $dealerId)
            ->assertJsonPath('meta.pagination.per_page', 10);
    }
}
