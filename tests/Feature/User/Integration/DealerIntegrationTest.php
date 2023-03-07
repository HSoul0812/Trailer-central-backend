<?php

namespace Tests\Feature\User\Integration;

use App\Models\Inventory\Inventory;
use App\Models\User\AuthToken;
use App\Models\User\Integration\DealerIntegration;
use App\Models\Integration\Integration;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User\User;

abstract class DealerIntegrationTest extends TestCase
{
    use WithFaker;

    protected const VERB = '';
    protected const ENDPOINT = '';

    protected function itShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->json(static::VERB, static::ENDPOINT)->assertStatus(403);
    }

    protected function tearDownSeed(int $dealerId): void
    {
        Inventory::query()->where('dealer_id', $dealerId)->delete();
        DealerIntegration::query()->where('dealer_id', $dealerId)->delete();
        AuthToken::query()->where('user_id', $dealerId)->delete();
        User::query()->where('dealer_id', $dealerId)->delete();
    }

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
     * @param string $integrationCode
     * @param array $attributes
     * @return array{dealer: User, dealer_integration: DealerIntegration, token: AuthToken}
     */
    protected function createDealerIntegration(string $integrationCode, array $attributes = []): array
    {
        /** @var Integration $integration */
        $integration = Integration::query()->where('code', $integrationCode)->firstOrFail();

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
