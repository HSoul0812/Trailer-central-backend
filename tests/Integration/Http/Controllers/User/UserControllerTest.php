<?php

namespace Tests\Integration\Http\Controllers\User;

use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Models\User\User;
use App\Nova\Resources\Dealer\Dealer;
use Illuminate\Foundation\Testing\WithFaker;
use Str;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFaker();
    }

    public function testItAllowListingDealersByNameWithProperAccessToken()
    {
        $dealer = factory(User::class)->create();

        $user = Integration::create([
            'name' => $this->faker->name(),
        ]);

        $user->perms()->create([
            'feature' => 'get_dealers_by_name',
            'permission_level' => 'can_see',
        ]);

        $token = Str::random(AuthToken::INTEGRATION_ACCESS_TOKEN_LENGTH);

        $user->authToken()->create([
            'user_type' => AuthToken::USER_TYPE_INTEGRATION,
            'access_token' => $token,
        ]);

        $this
            ->getJson("/api/users-by-name?name=$dealer", [
                'access-token' => $token,
            ])
            ->assertOk()
            ->assertSeeText($dealer->name);
    }
}
