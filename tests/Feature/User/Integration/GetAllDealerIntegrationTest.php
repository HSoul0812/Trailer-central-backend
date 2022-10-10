<?php

namespace Tests\Feature\User\Integration;

use App\Models\Inventory\Inventory;

class GetAllDealerIntegrationTest extends DealerIntegrationTest
{
    protected const VERB = 'GET';
    protected const ENDPOINT = '/api/user/integrations';

    /**
     * @var array
     */
    protected const DATA_STRUCTURE = [
        'data' => [
            '*' => [
                'id',
                'name',
                'description',
                'listing_count',
                'domain',
                'create_account_url',
                'created_at',
                'updated_at',
                'last_run_at',
                'active',
                'settings' => [
                    '*'
                ],
                'location_ids' => [
                    '*'
                ],
                'values' => [
                    '*'
                ],
            ]
        ]
    ];

    public function testShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->json(
            static::VERB,
            str_replace('{id}', $this->faker->numberBetween(3, 3000), static::ENDPOINT)
        )->assertStatus(403);
    }

    public function testShouldAccessWithAuthentication(): void
    {
        ['dealer' => $dealer, 'token' => $token] = $this->generateDealer();

        $this->withHeaders(['access-token' => $token->access_token])->json(
            static::VERB,
            str_replace('{id}', $this->faker->numberBetween(3, 3000), static::ENDPOINT)
        )->assertStatus(200);

        $this->tearDownSeed($dealer->dealer_id);
    }

    /**
     * Tests if the data returned is in the expected structure
     *
     * @return void
     */
    public function testIndexStructure(): void
    {
        ['dealer' => $dealer, 'token' => $token] = $this->generateDealer();

        $response = $this->withHeaders(['access-token' => $token->access_token])->get(self::ENDPOINT);
        $response->assertJsonStructure(self::DATA_STRUCTURE);

        $this->tearDownSeed($dealer->dealer_id);
    }
}
