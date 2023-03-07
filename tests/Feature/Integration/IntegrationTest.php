<?php

namespace Tests\Feature\Integration;

use App\Models\Inventory\Inventory;
use App\Models\User\AuthToken;
use App\Models\User\Integration\DealerIntegration;
use App\Models\Integration\Integration;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User\User;

/**
 * Class IntegrationTest
 * @package Tests\Feature\Integration
 */
abstract class IntegrationTest extends TestCase
{
    use WithFaker;

    protected const VERB = '';
    protected const ENDPOINT = '';

    protected function itShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->json(static::VERB, static::ENDPOINT)->assertStatus(403);
    }

    /**
     * Delete all created test data
     * @param int $dealerId
     * @return void
     */
    protected function tearDownSeed(int $dealerId): void
    {
        Inventory::query()->where('dealer_id', $dealerId)->delete();
        DealerIntegration::query()->where('dealer_id', $dealerId)->delete();
        AuthToken::query()->where('user_id', $dealerId)->delete();
        User::query()->where('dealer_id', $dealerId)->delete();
    }

    /**
     * Generate new dealer and return it with access token
     * @return void
     */
    protected function generateDealer(): array
    {
        $dealer = factory(User::class)->create();

        $token = factory(AuthToken::class)->create([
            'user_id' => $dealer->dealer_id,
            'user_type' => 'dealer',
        ]);

        return [
            'dealer' => $dealer,
            'token' => $token
        ];
    }

    /**
     * Creates a dealer integration
     * @param string $integrationId
     * @param array $attributes
     * @return array{dealer: User, dealer_integration: DealerIntegration, token: AuthToken}
     */
    protected function createDealerIntegration(string $integrationId, array $attributes = []): array
    {
        /** @var Integration $integration */
        $integration = Integration::query()->where('integration_id', $integrationId)->firstOrFail();

        ['token' => $token, 'dealer' => $dealer] = $this->generateDealer();

        $dealerIntegration = factory(DealerIntegration::class)->create(
            array_merge(
                [
                    'dealer_id' => $dealer->dealer_id,
                    'integration_id' => $integration->integration_id
                ],
                $attributes
            )
        );

        return [
            'dealer' => $dealer,
            'dealer_integration' => $dealerIntegration,
            'token' => $token
        ];
    }
}
