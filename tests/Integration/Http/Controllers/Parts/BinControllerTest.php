<?php

namespace Tests\Integration\Http\Controllers\Parts;

use Tests\database\seeds\Part\BinSeeder;
use Tests\TestCase;

class BinControllerTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     */
    public function testUpdate()
    {
        $seeder = new BinSeeder();
        $seeder->seed(1);

        $bin = $seeder->bins->first();

        $this->assertNotEquals('SomeBinName', $bin->bin_name);

        $response = $this->postJson('/api/parts/bins/' . $bin->id, [
            'location' => $bin->location,
            'bin_name' => 'SomeBinName'
        ], [
            'access-token' => $seeder->getAccessToken()
        ]);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);
        $data = $responseJson['data'];

        $this->assertEquals($bin->id, $data['id']);
        $this->assertEquals('SomeBinName', $data['name']);
        $this->assertArrayHasKey('dealer_id', $data);
        $this->assertArrayHasKey('uncompletedCycleCounts', $data);
    }
}
