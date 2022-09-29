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

        $response->assertStatus(200);
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

        $json = json_decode($response->getContent(), true);

        self::assertTrue(isset($json['data']));
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

        $json = json_decode($response->getContent(), true);

        self::assertNotTrue(isset($json['data'][0]['purchases']));
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

        $json = json_decode($response->getContent(), true);

        self::assertTrue(isset($json['data'][0]['purchaseOrders']));
        self::assertIsArray($json['data'][0]['purchaseOrders']);
        self::assertArrayHasKey('data', $json['data'][0]['purchaseOrders']);
        self::assertIsArray($json['data'][0]['purchaseOrders']['data']);
        self::assertArrayHasKey('meta', $json['data'][0]['purchaseOrders']);
        self::assertArrayHasKey('has_not_completed', $json['data'][0]['purchaseOrders']['meta']);
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

        $seeder->cleanUp();
    }
}
