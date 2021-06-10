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
     */
    public function testIndex()
    {
        $seeder = new RefundSeeder(['withRefund' => true]);
        $seeder->seed();

        $response = $this->json('GET', '/api/dms/refunds', [], ['access-token' => $seeder->authToken->access_token]);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseJson);
        $this->assertNotEmpty($responseJson['data']);
        $this->assertCount(2, $responseJson['data']);

        $this->assertArrayHasKey(0, $responseJson['data']);
        $this->assertArrayHasKey('meta', $responseJson['data']);
        $this->assertArrayHasKey('pagination', $responseJson['data']['meta']);

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
     * @covers ::index
     */
    public function testIndexAccessToken()
    {
        $response = $this->json('GET', '/api/dms/refunds');
        $response->assertStatus(403);
    }
}
