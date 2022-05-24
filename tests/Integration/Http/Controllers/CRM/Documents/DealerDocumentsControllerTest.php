<?php

namespace Tests\Integration\Http\Controllers\CRM\Documents;

use Tests\database\seeds\CRM\Documents\DealerDocumentsSeeder;
use Tests\Integration\IntegrationTestCase;

/**
 * Class DealerDocumentsControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Documents
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Documents\DealerDocumentsController
 */
class DealerDocumentsControllerTest extends IntegrationTestCase
{
    /**
     * @covers ::index
     * @group CRM
     */
    public function testIndex()
    {
        $leadTradeSeeder = new DealerDocumentsSeeder();
        $leadTradeSeeder->seed();

        $params = ['lead_id' => $leadTradeSeeder->lead->getKey()];

        $response = $this->json(
            'GET',
            '/api/user/documents',
            $params,
            ['access-token' => $leadTradeSeeder->authToken->access_token]
        );

        $leadTrades = $leadTradeSeeder->documents;

        $this->assertResponseDataEquals($response, $leadTrades, false);

        $leadTradeSeeder->cleanUp();
    }

    /**
     * @covers ::index
     * @group CRM
     */
    public function testIndexWithWrongAccessToken()
    {
        $leadTradeSeeder = new DealerDocumentsSeeder();
        $leadTradeSeeder->seed();

        $params = ['lead_id' => $leadTradeSeeder->lead->getKey()];

        $response = $this->json(
            'GET',
            '/api/user/documents',
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
        $leadTradeSeeder = new DealerDocumentsSeeder();
        $leadTradeSeeder->seed();

        $response = $this->json(
            'GET',
            '/api/user/documents',
            [],
            ['access-token' => $leadTradeSeeder->authToken->access_token]
        );

        $response->assertStatus(422);

        $leadTradeSeeder->cleanUp();
    }
}
