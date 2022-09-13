<?php

namespace Tests\Integration\Http\Controllers\Dms;

use Tests\database\seeds\Dms\RefundSeeder;
use Tests\TestCase;

/**
 * Class RefundControllerTest
 * @package Tests\Integration\Http\Controllers\Dms
 *
 * @coversDefaultClass \App\Http\Controllers\v1\Dms\RefundController
 */
class RefundControllerTest extends TestCase
{
    /**
     * @covers ::index
     *
     * @group DMS
     * @group DMS_REFUND
     */
    public function testIndex()
    {
        $seeder = new RefundSeeder(['withRefund' => true]);
        $seeder->seed();

        $response = $this->json('GET', '/api/dms/refunds', [], ['access-token' => $seeder->authToken->access_token]);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseJson);
        $this->assertArrayHasKey('meta', $responseJson);
        $this->assertArrayHasKey('pagination', $responseJson['meta']);

        $this->assertNotEmpty($responseJson['data']);
        $this->assertCount(1, $responseJson['data']);
        $this->assertArrayHasKey(0, $responseJson['data']);

        $currentItem = $responseJson['data'][0];

        $this->assertArrayHasKey('tb_name', $currentItem);
        $this->assertArrayHasKey('tb_primary_id', $currentItem);
        $this->assertArrayHasKey('amount', $currentItem);
        $this->assertArrayHasKey('created_at', $currentItem);
        $this->assertArrayHasKey('updated_at', $currentItem);

        $this->assertEquals($seeder->refund->tb_primary_id, $currentItem['tb_primary_id']);
        $this->assertEquals($seeder->refund->amount, $currentItem['amount']);
        $this->assertEquals($seeder->refund->tb_name, $currentItem['tb_name']);

        $seeder->cleanUp();
    }

    /**
     * @covers ::show
     *
     * @group DMS
     * @group DMS_REFUND
     */
    public function testShow()
    {
        $seeder = new RefundSeeder(['withRefund' => true]);
        $seeder->seed();

        $response = $this->json('GET', '/api/dms/refunds/' . $seeder->refund->id, [], ['access-token' => $seeder->authToken->access_token]);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertNotEmpty($responseJson['data']);

        $currentItem = $responseJson['data'];

        $this->assertArrayHasKey('id', $currentItem);
        $this->assertArrayHasKey('tb_name', $currentItem);
        $this->assertArrayHasKey('tb_primary_id', $currentItem);
        $this->assertArrayHasKey('amount', $currentItem);
        $this->assertArrayHasKey('created_at', $currentItem);
        $this->assertArrayHasKey('updated_at', $currentItem);

        $this->assertSame($seeder->refund->id, $currentItem['id']);

        $seeder->cleanUp();
    }

    /**
     * @covers ::show
     *
     * @group DMS
     * @group DMS_REFUND
     */
    public function testShowWrongId()
    {
        $seeder1 = new RefundSeeder(['withRefund' => true]);
        $seeder1->seed();

        $seeder2 = new RefundSeeder(['withRefund' => true]);
        $seeder2->seed();

        $response = $this->json('GET', '/api/dms/refunds/' . $seeder2->refund->id, [], ['access-token' => $seeder1->authToken->access_token]);

        $response->assertStatus(400);

        $seeder1->cleanUp();
        $seeder2->cleanUp();
    }

    /**
     * @covers ::index
     *
     * @group DMS
     * @group DMS_REFUND
     */
    public function testIndexWithoutAccessToken()
    {
        $response = $this->json('GET', '/api/dms/refunds');
        $response->assertStatus(403);
    }

    /**
     * @covers ::show
     *
     * @group DMS
     * @group DMS_REFUND
     */
    public function testShowWithoutAccessToken()
    {
        $response = $this->json('GET', '/api/dms/refunds');
        $response->assertStatus(403);
    }
}
