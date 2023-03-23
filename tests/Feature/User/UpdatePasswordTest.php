<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Feature\User;

use App\Models\User\AuthToken;
use App\Models\User\DealerUser;
use App\Models\User\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdatePasswordTest extends TestCase
{
    use WithFaker;

    /** @var User */
    protected $dealer;

    /** @var DealerUser */
    protected $dealerUser;

    /** @var AuthToken */
    protected $token;

    /** @var string */
    protected $password;

    /** @var string */
    protected $salt;

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testUpdatePasswordUsingWrongVerb(): void
    {
        $response = $this->json('PUT', '/api/user/password/update', ['current_password' => $this->password, 'password' => $this->faker->password(6, 8)]);

        $response->assertStatus(403);
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     *
     * @throws \Exception
     */
    public function testUpdatePasswordNonexistentUser(): void
    {
        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $this->dealer->delete();

        $response = $this->json(
            'PUT',
            '/api/user/password/update',
            [
                'current_password' => $this->password,
                'password' => $this->faker->password(6, 8),
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(403);
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testUpdatePasswordForUserWithDealerType(): void
    {
        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $response = $this->json(
            'PUT',
            '/api/user/password/update',
            [
                'current_password' => $this->password,
                'password' => '{1A3Q2gm@s',
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        $this->assertNotSame($this->dealer->password, $this->dealer->fresh()->password);
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testUpdatePasswordForUserWithDealerTypeWithShortPassword(): void
    {
        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $response = $this->json(
            'PUT',
            '/api/user/password/update',
            [
                'current_password' => $this->password,
                'password' => 'sgXRuwaa',
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(422);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertArrayHasKey('password', $json['errors']);

        $this->assertSame('Validation Failed', $json['message']);
        $this->assertContains(
            'The password must be at least 8 characters and contain at least one number.',
            $json['errors']['password']
        );
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testUpdatePasswordForUserWithDealerTypeWithWrongCurrentPassword(): void
    {
        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $response = $this->json(
            'PUT',
            '/api/user/password/update',
            [
                'current_password' => $this->faker->password(5),
                'password' => 'PP(HuF(9gF',
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(500);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);

        $this->assertSame('The current password is wrong!', $json['message']);
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testUpdatePasswordForUserWithDealerUserType(): void
    {
        $this->dealerUser = factory(DealerUser::class)->create([
            'dealer_id' => $this->dealer->dealer_id,
            'password' => $this->dealer->password,
            'salt' => $this->salt,
        ]);

        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealerUser->dealer_user_id,
            'user_type' => AuthToken::USER_TYPE_DEALER_USER,
        ]);

        $response = $this->json(
            'PUT',
            '/api/user/password/update',
            [
                'current_password' => $this->password,
                'password' => 'PP(HuF(9gF',
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        $this->assertNotSame($this->dealer->password, $this->dealerUser->fresh()->password);
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testUpdatePasswordForUserWithDealerUserTypeWithShortPassword(): void
    {
        $this->dealerUser = factory(DealerUser::class)->create([
            'dealer_id' => $this->dealer->dealer_id,
        ]);

        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealerUser->dealer_user_id,
            'user_type' => AuthToken::USER_TYPE_DEALER_USER,
        ]);

        $response = $this->json(
            'PUT',
            '/api/user/password/update',
            [
                'current_password' => $this->password,
                'password' => 'NfbL6Ut',
            ],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(422);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertArrayHasKey('password', $json['errors']);

        $this->assertSame('Validation Failed', $json['message']);
        $this->assertContains(
            'The password must be at least 8 characters and contain at least one special character.',
            $json['errors']['password']
        );
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->password = $this->faker->password(6, 8);
        $this->salt = uniqid();

        $this->dealer = factory(User::class)->create([
            'password' => $this->password,
            'salt' => $this->salt,
        ]);
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
