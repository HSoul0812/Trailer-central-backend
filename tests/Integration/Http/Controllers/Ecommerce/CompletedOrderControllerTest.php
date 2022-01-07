<?php
namespace Tests\Integration\Http\Controllers\Ecommerce;

use Tests\database\seeds\Ecommerce\CompletedOrderSeeder;
use Tests\TestCase;

class CompletedOrderControllerTest extends TestCase
{
    public function testShowAction()
    {
        $seeder = new CompletedOrderSeeder();
        $seeder->seed();

        $response = $this->json('GET', '/api/ecommerce/orders/' . $seeder->completedOrder[0]->id, [], [
            'access-token' => $seeder->authToken->access_token
        ]);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $data = $responseJson['data'];

        $this->assertEquals($seeder->completedOrder[0]->id, $data['id']);
        $this->assertArrayHasKey('parts', $data);
        $this->assertArrayHasKey('object_id', $data);
    }
}