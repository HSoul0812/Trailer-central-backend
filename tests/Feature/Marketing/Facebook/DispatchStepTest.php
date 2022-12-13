<?php

namespace Tests\Feature\Marketing;

use Tests\TestCase;
use Tests\database\seeds\Marketing\Facebook\MarketplaceSeeder;


class DispatchStepTest extends TestCase
{
    const API_ENDPOINT = '/api/dispatch/facebook/';

    /**
     * @group Marketing
     */
    public function testGetToken()
    {
        $response = $this->json('POST', self::API_ENDPOINT, [
                'ip_address' => '127.0.0.1',
                'client_uuid' => 'fbm9999999999999',
                'version' => '0.0.1'
            ])->assertStatus(200)
            ->assertJsonStructure(['data'])->decodeResponseJson();

        return $response['data'];
    }
    
    /**
     * @group Marketing
     * @depends testGetToken
     */
    public function testGettingData($fbAccessToken)
    {
        // Seed Marketplace Data
        $this->seeder->seed();

        // get initial integration data
        $initialData = $this->withHeaders(['access-token' => $fbAccessToken])
            ->json('GET', self::API_ENDPOINT) 
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'config',
                    'dealers',
                    'tunnels'
                ]
            ])->decodeResponseJson();

        $integrations = $initialData['data']['dealers']['data'];
        $integrationId = $integrations[0]['integration'];
        $integrationIds = array_column($integrations, 'integration');

        // send login step
        $this->withHeaders(['access-token' => $fbAccessToken])
            ->json('PUT', self::API_ENDPOINT . $integrationId, [
                'action' => 'choose',
                'logs' => '[]',
                'step' => 'login-fb'
            ])->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'action',
                    'inventory_id',
                    'selectors',
                    'status',
                    'step'
                ]
            ]);

        // check integration data after login step
        $this->withHeaders(['access-token' => $fbAccessToken])
            ->json('GET', self::API_ENDPOINT) 
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'config',
                    'dealers',
                    'tunnels'
                ]
            ])
            ->assertJsonMissingExact([
                'data.dealers.data.*.integration' => $integrationId
            ])->assertJsonCount(count($integrations) - 1, 'data.dealers.data.*');

        // send stop step
        $this->withHeaders(['access-token' => $fbAccessToken])
            ->json('PUT', self::API_ENDPOINT . $integrationId, [
                'action' => 'choose',
                'logs' => '[]',
                'step' => 'stop-script'
            ])->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'action',
                    'inventory_id',
                    'selectors',
                    'status',
                    'step'
                ]
        ]);

        // check integration data after stop step
        $this->withHeaders(['access-token' => $fbAccessToken])
            ->json('GET', self::API_ENDPOINT) 
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'config',
                    'dealers',
                    'tunnels'
                ]
            ])
            ->assertJsonPath('data.dealers.data.*.integration', $integrationIds)
            ->assertJsonCount(count($integrations), 'data.dealers.data.*');
    }


    /**
     * Set Up Seeder
     * 
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Make Profile Seeder
        $this->seeder = new MarketplaceSeeder();
    }

    /**
     * Tear Down Seeder
     * 
     * @return void
     */
    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }
}