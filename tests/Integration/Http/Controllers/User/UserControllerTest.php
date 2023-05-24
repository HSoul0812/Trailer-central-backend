<?php

namespace Tests\Integration\Http\Controllers\User;

use App\Models\User\AuthToken;
use App\Models\User\Integration\Integration;
use App\Models\User\User;
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
            ->getJson("/api/users-by-name?name=$dealer->name", [
                'access-token' => $token,
            ])
            ->assertOk()
            ->assertJsonPath('data.0.id', $dealer->dealer_id)
            ->assertJsonPath('data.0.name', $dealer->name)
            ->assertSeeText($dealer->name);
    }

    public function testItAllowCreateDealerWithProperIntegrationToken()
    {
        $user = Integration::create([
            'name' => $this->faker->name(),
        ]);

        $user->perms()->create([
            'feature' => 'create_user',
            'permission_level' => 'can_see_and_change',
        ]);

        $token = Str::random(AuthToken::INTEGRATION_ACCESS_TOKEN_LENGTH);

        $user->authToken()->create([
            'user_type' => AuthToken::USER_TYPE_INTEGRATION,
            'access_token' => $token,
        ]);

        $name = $this->faker->name();
        $email = $this->faker->email();
        $this
            ->post("/api/users", [
                'name' => $name,
                'email' => $email,
                'password' => 'abcdefg12345678',
                'from' => 'trailertrader',
                'clsf_active' => 1
            ], [
                'access-token' => $token,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.name', $name)
            ->assertJsonPath('data.email', $email);
    }

    public function testItAllowGettingTrailerTradeDealersWithProperAccessToken()
    {
        $dealer = factory(User::class)->create([
            'clsf_active' => true,
        ]);

        $user = Integration::create([
            'name' => $this->faker->name(),
        ]);

        $user->perms()->create([
            'feature' => 'get_dealers_of_trailertrade',
            'permission_level' => 'can_see',
        ]);

        $token = Str::random(AuthToken::INTEGRATION_ACCESS_TOKEN_LENGTH);

        $user->authToken()->create([
            'user_type' => AuthToken::USER_TYPE_INTEGRATION,
            'access_token' => $token,
        ]);

        $response = $this->getJson("/api/tt-dealers", [
            'access-token' => $token,
        ]);
        
        $response
            ->assertOk()
            ->assertJsonPath('data.0.clsf_active', true)
            ->assertSeeText($dealer->name);
        
        foreach($response['data'] as $value) {
            $this->assertTrue($value['clsf_active']);
        }
    }
}
