<?php

namespace Tests\Feature\User;

use App\Mail\User\PasswordResetEmail;
use App\Models\User\DealerPasswordReset;
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

    public function testStartPasswordReset()
    {
        $this->dealer = $this->dealer->fresh();

        $response = $this->json('POST', '/api/user/password-reset/start', ['email' => $this->dealer->email]);

        $response->assertStatus(201);
    }

    public function testStartPasswordResetNoEmail()
    {
        $this->dealer = $this->dealer->fresh();

        $response = $this->json('POST', '/api/user/password-reset/start', []);

        $response->assertStatus(422);
    }

    public function testStartPasswordResetNonExistentEmail()
    {
        $this->dealer = $this->dealer->fresh();

        $response = $this->json('POST', '/api/user/password-reset/start', ['email' => self::NON_EXISTENT_EMAIL]);

        $response->assertStatus(201);
    }

    public function testFinishPasswordReset()
    {
        $this->dealer = $this->dealer->fresh();

        $passwordReset = $this->assertResetPasswordWasSent();

        $password = uniqid();

        $response = $this->json('POST', '/api/user/password-reset/finish', ['code' => $passwordReset->code, 'password' => $password]);
        $response->assertStatus(201);

        $response = $this->json('POST', '/api/user/login', ['email' => $this->dealer->email, 'password' => $password]);
        $response->assertStatus(200);
    }

    public function testFinishPasswordResetWrongPassword()
    {
        $this->dealer = $this->dealer->fresh();

        $passwordReset = $this->assertResetPasswordWasSent();

        $password = uniqid();

        $response = $this->json('POST', '/api/user/password-reset/finish', ['code' => $passwordReset->code, 'password' => $password]);
        $response->assertStatus(201);

        $response = $this->json('POST', '/api/user/login', ['email' => $this->dealer->email, 'password' => 'wrongpassword']);
        $response->assertStatus(400);
    }

    public function testFinishPasswordResetNoPassword()
    {
        $this->dealer = $this->dealer->fresh();

        $passwordReset = $this->assertResetPasswordWasSent();

        $response = $this->json('POST', '/api/user/password-reset/finish', ['code' => $passwordReset->code]);
        $response->assertStatus(422);
    }

    public function testFinishPasswordResetNoCode()
    {
        $this->dealer = $this->dealer->fresh();

        $this->assertResetPasswordWasSent();

        $password = uniqid();

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
