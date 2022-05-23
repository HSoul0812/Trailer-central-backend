<?php

namespace Tests\Integration\Http\Controllers\CRM\Leads;

use Tests\database\seeds\CRM\Leads\LeadTradeSeeder;
use Tests\Integration\IntegrationTestCase;

/**
 * Class LeadTradeControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Leads
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Leads\LeadTradeController
 */
class LeadTradeControllerTest extends IntegrationTestCase
{
    /**
     * @covers ::index
     * @group CRM
     */
    public function testIndex()
    {
        $leadTradeSeeder = new LeadTradeSeeder();
        $leadTradeSeeder->seed();

        $params = ['lead_id' => $leadTradeSeeder->lead->getKey()];

        $response = $this->json(
            'GET',
            '/api/leads/trades?with=images',
            $params,
            ['access-token' => $leadTradeSeeder->authToken->access_token]
        );

        $leadTrades = $leadTradeSeeder->leadTrades;

        $this->assertResponseDataEquals($response, $leadTrades, false);

        $leadTradeSeeder->cleanUp();
    }

    /**
     * @covers ::index
     * @group CRM
     */
    public function testIndexWithWrongAccessToken()
    {
        $leadTradeSeeder = new LeadTradeSeeder();
        $leadTradeSeeder->seed();

        $params = ['lead_id' => $leadTradeSeeder->lead->getKey()];

        $response = $this->json(
            'GET',
            '/api/leads/trades?with=images',
            $params,
            ['access-token' => 'wrong_access_token']
        );

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');

        $leadTradeSeeder->cleanUp();
    }

    /**
     * @covers ::index
     * @group CRM
     */
    public function testIndexWithoutLeadId()
    {
        $leadTradeSeeder = new LeadTradeSeeder();
        $leadTradeSeeder->seed();

        $response = $this->json(
            'GET',
            '/api/leads/trades?with=images',
            [],
            ['access-token' => $leadTradeSeeder->authToken->access_token]
        );

        $response->assertStatus(422);

        $leadTradeSeeder->cleanUp();
    }
}
