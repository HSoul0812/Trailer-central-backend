<?php


namespace Integration\Http\Controllers\User;


use Illuminate\Http\JsonResponse;
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
        $response = $this->json('GET', "/api/user/dealer-location/$locationId/mileage-fee");
        $response->assertStatus(JsonResponse::HTTP_OK);
        $fees = $response->getOriginalContent();
        $this->assertCount(1, $fees);
        $this->assertEquals($fees[0]->getKey(), $location->mileageFees[0]->getKey());
    }

    /**
     * @covers ::create
     */
    public function testCreate() {

    }

    /**
     * @covers ::update
     */
    public function testUpdate() {

    }

    /**
     * @covers ::delete
     */
    public function testDelete() {

    }
}
