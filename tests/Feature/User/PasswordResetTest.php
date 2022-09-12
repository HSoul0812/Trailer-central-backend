<?php

namespace Tests\Feature\User;

use App\Mail\User\PasswordResetEmail;
use App\Models\User\DealerPasswordReset;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User\User;
use App\Models\User\AuthToken;
use App\Repositories\User\DealerPasswordResetRepositoryInterface;

/**
 * Class PasswordResetTest
 * @package Tests\Feature\User
 * @todo add test cases for salesperson password reset which is not supported right now
 */
class PasswordResetTest extends TestCase
{
    use WithFaker;

    private const NON_EXISTENT_EMAIL = 'bestdeveverinthehistoryofdev@bestdev.com';

    protected $dealer;

    /**
     * App\Repositories\User\DealerPasswordResetRepositoryInterface
     */
    protected $passwordResetRepo;

    public function setUp(): void
    {
        parent::setUp();

        $this->dealer = factory(User::class)->create();

        factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => 'dealer',
        ]);

        $this->passwordResetRepo = app(DealerPasswordResetRepositoryInterface::class);
    }

    public function tearDown(): void
    {
        $this->dealer->delete();

        parent::tearDown();
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testStartPasswordReset()
    {
        $this->dealer = $this->dealer->fresh();

        $response = $this->json('POST', '/api/user/password-reset/start', ['email' => $this->dealer->email]);

        $response->assertStatus(201);
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testStartPasswordResetNoEmail()
    {
        $this->dealer = $this->dealer->fresh();

        $response = $this->json('POST', '/api/user/password-reset/start', []);

        $response->assertStatus(422);
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testStartPasswordResetNonExistentEmail()
    {
        $this->dealer = $this->dealer->fresh();

        $response = $this->json('POST', '/api/user/password-reset/start', ['email' => self::NON_EXISTENT_EMAIL]);

        $response->assertStatus(201);
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testFinishPasswordReset()
    {
        $this->dealer = $this->dealer->fresh();

        $passwordReset = $this->assertResetPasswordWasSent();

        $password = $this->faker->password(6, 8);

        $response = $this->json('POST', '/api/user/password-reset/finish', ['code' => $passwordReset->code, 'password' => $password]);
        $response->assertStatus(201);

        $response = $this->json('POST', '/api/user/login', ['email' => $this->dealer->email, 'password' => $password]);
        $response->assertStatus(200);
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testFinishPasswordResetWrongPasswordLength(): void
    {
        $this->dealer = $this->dealer->fresh();

        $passwordReset = $this->assertResetPasswordWasSent();

        $password = $this->faker->password(9);

        $response = $this->json('POST', '/api/user/password-reset/finish', ['code' => $passwordReset->code, 'password' => $password]);
        $response->assertStatus(422);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertArrayHasKey('password', $json['errors']);

        $this->assertSame('Validation Failed', $json['message']);
        $this->assertContains('The password should not be greater than 8 characters.', $json['errors']['password']);
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testFinishPasswordResetNoPassword()
    {
        $this->dealer = $this->dealer->fresh();

        $passwordReset = $this->assertResetPasswordWasSent();

        $response = $this->json('POST', '/api/user/password-reset/finish', ['code' => $passwordReset->code]);
        $response->assertStatus(422);
    }

    /**
     * @group DMS
     * @group DMS_USER_PASSWORD
     *
     * @return void
     */
    public function testFinishPasswordResetNoCode()
    {
        $this->dealer = $this->dealer->fresh();

        $this->assertResetPasswordWasSent();

        $password = $this->faker->password(6, 8);

        $response = $this->json('POST', '/api/user/password-reset/finish', ['password' => $password]);
        $response->assertStatus(422);
    }

    private function assertResetPasswordWasSent(): DealerPasswordReset
    {
        Mail::fake();

        $passwordReset = $this->passwordResetRepo->initiatePasswordReset($this->dealer);

        Mail::assertSent(PasswordResetEmail::class, function ($mail) use ($passwordReset) {
            return $mail->data['code'] === $passwordReset->code && $mail->data['resetUrl'] === config('password-reset.email.endpoint');
        });

        return $passwordReset;
    }
}
