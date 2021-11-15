<?php

namespace Tests\Feature\Inventory\CustomOverlay;

use App\Models\Inventory\CustomOverlay;
use Tests\TestCase;
use App\Models\User\User;
use App\Models\User\AuthToken;

class EndpointCustomOverlaysTest extends TestCase
{
    protected const VERB = '';
    protected const ENDPOINT = '';

    protected function itShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->json(static::VERB, static::ENDPOINT)->assertStatus(403);
    }

    /** @var array{dealer: User, overlays: array<CustomOverlay>, token: AuthToken} */
    protected $seed;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed = $this->createDealerWithCustomerOverlays();
    }

    public function tearDown(): void
    {
        $this->tearDownSeed($this->seed['dealer']->dealer_id);

        parent::tearDown();
    }

    protected function tearDownSeed(int $dealerId): void
    {
        CustomOverlay::query()->where('dealer_id', $dealerId)->delete();
        AuthToken::query()->where('user_id', $dealerId)->delete();
        User::query()->where('dealer_id', $dealerId)->delete();
    }

    /**
     * @return array{dealer: User, overlays: array<CustomOverlay>, token: AuthToken}
     */
    protected function createDealerWithCustomerOverlays(): array
    {
        $dealer = factory(User::class)->create();

        $token = factory(AuthToken::class)->create([
            'user_id' => $dealer->dealer_id,
            'user_type' => 'dealer',
        ]);

        $overlays = [];

        foreach (CustomOverlay::VALID_CUSTOM_NAMES as $name) {
            $overlays[] = factory(CustomOverlay::class)->create(['dealer_id' => $dealer->dealer_id, 'name' => $name]);
        }

        return [
            'dealer' => $dealer,
            'token' => $token,
            'overlays' => $overlays
        ];
    }
}
