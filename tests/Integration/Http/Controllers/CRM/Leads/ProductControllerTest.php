<?php

namespace Tests\Integration\Http\Controllers\CRM\Leads;

use Tests\database\seeds\CRM\Leads\ProductSeeder;
use Tests\Integration\IntegrationTestCase;

/**
 * Class ProductControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Leads
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Leads\ProductController
 */
class ProductControllerTest extends IntegrationTestCase
{
    /**
     * @covers ::index
     * @group CRM
     */
    public function testIndex()
    {
        $productSeeder = new ProductSeeder();
        $productSeeder->seed();

        $params = ['website_lead_id' => $productSeeder->lead->getKey()];

        $response = $this->json(
            'GET',
            '/api/leads/products',
            $params,
            ['access-token' => $productSeeder->authToken->access_token]
        );

        $inventoryLeads = $productSeeder->inventoryLeads;

        $this->assertResponseDataEquals($response, $inventoryLeads, false);

        $productSeeder->cleanUp();
    }

    /**
     * @covers ::index
     * @group CRM
     */
    public function testIndexWrongAccessToken()
    {
        $productSeeder = new ProductSeeder();
        $productSeeder->seed();

        $params = ['website_lead_id' => $productSeeder->lead->getKey()];

        $response = $this->json(
            'GET',
            '/api/leads/products',
            $params,
            ['access-token' => 'wrong_access_token']
        );

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');

        $productSeeder->cleanUp();
    }
}
