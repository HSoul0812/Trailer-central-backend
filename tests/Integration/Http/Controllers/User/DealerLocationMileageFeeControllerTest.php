<?php


namespace Integration\Http\Controllers\User;


use App\Http\Controllers\v1\User\DealerLocationMileageFeeController;
use App\Models\User\DealerLocationMileageFee;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Tests\database\seeds\User\DealerLocationSeeder;
use Tests\TestCase;

/**
 * Class DealerLocationMileageFeeControllerTest
 * @coversDefaultClass App\Http\Controllers\v1\User\DealerLocationMileageFeeController
 * @package Integration\Http\Controllers\User
 */
class DealerLocationMileageFeeControllerTest extends TestCase
{
    /**
     * @var DealerLocationSeeder
     */
    private $seeder;

    public function setUp(): void
    {
        parent::setUp();
        $this->seeder = new DealerLocationSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();
        parent::tearDown();
    }

    /**
     * @covers ::index
     */
    public function testIndex() {
        $this->seeder->seed();
        $dealer = $this->seeder->dealers[0];
        $location = $this->seeder->locations[$dealer->getKey()]->first();
        $locationId = $location->getKey();
        $request = new Request();
        $controller = app(DealerLocationMileageFeeController::class);
        $response = $controller->index($locationId, $request);
        $fees = $response->getOriginalContent();
        $this->assertCount(1, $fees);
        $this->assertEquals($fees[0]->getKey(), $location->mileageFees[0]->getKey());
    }

    /**
     * @covers ::create
     */
    public function testCreate() {
        $this->seeder->seed();
        $dealer = $this->seeder->dealers[0];
        $location = $this->seeder->locations[$dealer->getKey()]->first();
        $locationId = $location->getKey();
        $params = [
            'inventory_category_id' => 1,
            'fee_per_mile' => 1
        ];
        $request = new Request($params);
        $controller = app(DealerLocationMileageFeeController::class);
        $response = $controller->create($locationId, $request);
        $fee = $response->getOriginalContent();
        $this->assertEquals($fee->fee_per_mile, $params['fee_per_mile']);
        $this->assertEquals($fee->inventory_category_id, $params['inventory_category_id']);
    }

    /**
     * @covers ::update
     */
    public function testUpdate() {
        $this->seeder->seed();
        $dealer = $this->seeder->dealers[0];
        $location = $this->seeder->locations[$dealer->getKey()]->first();
        $locationId = $location->getKey();
        $feeId = $location->mileageFees[0]->getKey();

        $params = [
            'inventory_category_id' => 1,
            'fee_per_mile' => 10
        ];
        $request = new Request($params);
        $controller = app(DealerLocationMileageFeeController::class);
        $response = $controller->update($locationId, $feeId, $request);
        $this->assertEquals(
            $response->getOriginalContent()['response']['data']['id'],
            $feeId
        );
        $this->assertDatabaseHas(
            DealerLocationMileageFee::getTableName(),
            [
                'id' => $feeId,
                'inventory_category_id' => 1,
                'fee_per_mile' => 10
            ]
        );
    }

    /**
     * @covers ::delete
     */
    public function testDelete() {
        $this->seeder->seed();
        $dealer = $this->seeder->dealers[0];
        $location = $this->seeder->locations[$dealer->getKey()]->first();
        $feeId = $location->mileageFees[0]->getKey();

        $request = new Request();
        $controller = app(DealerLocationMileageFeeController::class);
        $controller->delete($feeId, $request);
        $this->assertDatabaseMissing(
            DealerLocationMileageFee::getTableName(),
            [
                'id' => $feeId
            ]
        );
    }
}
