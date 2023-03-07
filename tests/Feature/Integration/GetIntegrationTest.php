<?php

namespace Tests\Feature\Integration;

/**
 * Class GetIntegrationTest
 * @package Tests\Feature\Integration
 */
class GetIntegrationTest extends IntegrationTest
{
    protected const VERB = 'GET';
    protected const ENDPOINT = '/api/integrations';

    /**
     * @var array
     */
    protected const INTEGRATION_STRUCTURE = [
        'id',
        'code',
        'identifier',
        'module_name',
        'module_status',
        'name',
        'description',
        'domain',
        'create_account_url',
        'active',
        'filters' => [
            '*'
        ],
        'frequency',
        'last_run_at',
        'settings' => [
            '*'
        ],
        'include_sold',
        'send_email',
        'uses_staging',
        'show_for_integrated'
    ];

    /**
     * @var array
     */
    protected const DATA_STRUCTURE = [
        'data' => [
            '*' => self::INTEGRATION_STRUCTURE
        ]
    ];

    /**
     * @return void
     */
    public function testShouldPreventRetrievingIntegrationsWithoutAuthentication(): void
    {
        $this->json(
            static::VERB,
            static::ENDPOINT
        )->assertStatus(403);
    }

    /**
     * Test retrieving an integration by id without access-token
     * @return void
     */
    public function testShouldPreventRetrievingIntegrationByIdWithoutAuthentication(): void
    {
        $this->json(
            static::VERB,
            static::ENDPOINT . "/" . $this->faker->numberBetween(1, 100)
        )->assertStatus(403);
    }

    /**
     * Test retrieving integrations with access-token
     * @return void
     */
    public function testShouldRetrieveIntegrationsWithAuthentication(): void
    {
        ['dealer' => $dealer, 'token' => $token] = $this->generateDealer();

        $this->withHeaders(['access-token' => $token->access_token])->json(
            static::VERB,
            static::ENDPOINT
        )->assertStatus(200);

        $this->tearDownSeed($dealer->dealer_id);
    }

    /**
     * Test retrieving an integration by id with access-token
     * @return void
     */
    public function testShouldRetrieveIntegrationByIdWithAuthentication(): void
    {
        ['dealer' => $dealer, 'token' => $token] = $this->generateDealer();

        $this->withHeaders(['access-token' => $token->access_token])->json(
            static::VERB,
            static::ENDPOINT . "/" . $this->faker->numberBetween(1, 100)
        )->assertStatus(200);

        $this->tearDownSeed($dealer->dealer_id);
    }

    /**
     * Test invalid integration id on get request
     * @return void
     */
    public function testShouldReturnInvalidIntegrationById(): void
    {
        ['dealer' => $dealer, 'token' => $token] = $this->generateDealer();

        $response = $this->withHeaders(['access-token' => $token->access_token])->json(
            static::VERB,
            static::ENDPOINT . "/" . $this->faker->numberBetween(400, 3000)
        )->assertStatus(422);

        $json = json_decode($response->content(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('integration_id', $json['errors']);
        self::assertSame('The selected integration id is invalid.', $json['errors']['integration_id'][0]);

        $this->tearDownSeed($dealer->dealer_id);
    }

    /**
     * Test retrieving authenticated user integrated integrations
     * @return void
     */
    public function testShouldReturnIntegratedIntegrations(): void
    {
        ['dealer' => $dealer, 'token' => $token] = $this->createDealerIntegration($this->faker->numberBetween(1, 100));

        $this->withHeaders(['access-token' => $token->access_token])->json(
            static::VERB,
            static::ENDPOINT . "?integrated=true"
        )->assertStatus(200);

        $this->tearDownSeed($dealer->dealer_id);
    }

    /**
     * Test retrieving authenticated user no integrated integrations
     * @return void
     */
    public function testShouldReturnNoIntegratedIntegrations(): void
    {
        ['dealer' => $dealer, 'token' => $token] = $this->generateDealer();

        $this->withHeaders(['access-token' => $token->access_token])->json(
            static::VERB,
            static::ENDPOINT . "?integrated=false"
        )->assertStatus(200);

        $this->tearDownSeed($dealer->dealer_id);
    }

    /**
     * Test index structure
     * @return void
     */
    public function testValidateIntegrationIndexStructure(): void
    {
        ['dealer' => $dealer, 'token' => $token] = $this->generateDealer();

        $this->withHeaders(['access-token' => $token->access_token])->json(
            self::VERB,
            static::ENDPOINT
        )->assertStatus(200)->assertJsonStructure(self::DATA_STRUCTURE);

        $this->tearDownSeed($dealer->dealer_id);
    }

    /**
     * Test specific integration structure by id
     * @return void
     */
    public function testValidateIntegrationIndexStructureById(): void
    {
        ['dealer' => $dealer, 'token' => $token] = $this->generateDealer();

        $integrationRoute = static::ENDPOINT . "/" . $this->faker->numberBetween(1, 80);

        $this->withHeaders(['access-token' => $token->access_token])->json(
            self::VERB,
            $integrationRoute
        )->assertStatus(200)->assertJsonStructure(
            ['data' => self::INTEGRATION_STRUCTURE]
        );

        $this->tearDownSeed($dealer->dealer_id);
    }
}
