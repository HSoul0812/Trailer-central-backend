<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Feature\User;

use App\Models\User\DealerUser;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User\User;
use App\Models\User\AuthToken;

class UpdatePasswordTest extends TestCase
{
    use WithFaker;

    /** @var User */
    protected $dealer;

    /** @var DealerUser */
    protected $dealerUser;

    /** @var AuthToken */
    protected $token;

    public function testUpdatePasswordUsingWrongVerb(): void
    {
        $response = $this->json('PUT', '/api/user/password/update', ['password' => $this->faker->password()]);

        $response->assertStatus(403);
    }

    public function testUpdatePasswordNonexistentUser(): void
    {
        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $this->dealer->delete();

        $response = $this->json(
            'PUT', '/api/user/password/update',
            ['password' => $this->faker->password()],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(403);
    }

    public function testUpdatePasswordForUserWithDealerType(): void
    {
        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $response = $this->json(
            'PUT', '/api/user/password/update',
            ['password' => $this->faker->password()],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        $this->assertNotSame($this->dealer->password, $this->dealer->fresh()->password);
    }

    public function testUpdatePasswordForUserWithDealerUserType(): void
    {
        $this->dealerUser = factory(DealerUser::class)->create([
            'dealer_id' => $this->dealer->dealer_id
        ]);

        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealerUser->dealer_user_id,
            'user_type' => AuthToken::USER_TYPE_DEALER_USER
        ]);

        $response = $this->json(
            'PUT', '/api/user/password/update',
            ['password' => $this->faker->password()],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        $this->assertNotSame($this->dealer->password, $this->dealerUser->fresh()->password);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->dealer = factory(User::class)->create();
    }

    public function tearDown(): void
    {
        $this->dealer->delete();

        if ($this->token) {
            $this->token->delete();
        }

        if ($this->dealerUser) {
            $this->dealerUser->delete();
        }

        parent::tearDown();
    }
}
