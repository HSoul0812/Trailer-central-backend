<?php

namespace Tests\Integration\App\Services\WebsiteUser;

use App\DTOs\User\TcApiResponseUser;
use App\Models\WebsiteUser\WebsiteUser;
use App\Repositories\WebsiteUser\WebsiteUserRepository;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use App\Services\Captcha\Google\GoogleCaptchaService;
use App\Services\Integrations\TrailerCentral\Api\Users\UsersService;
use App\Services\WebsiteUser\AuthService;
use Doctrine\DBAL\Driver\PDO\Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use JetBrains\PhpStorm\Pure;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Common\IntegrationTestCase;
use Tymon\JWTAuth\Validators\TokenValidator;

class AuthServiceTest extends IntegrationTestCase
{
    private $captchaServiceMock;
    private $usersServiceMock;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    #[Pure] private function getConcreteService(): AuthService {
        $this->captchaServiceMock = $this->createMock(GoogleCaptchaService::class);
        $this->usersServiceMock = $this->createMock(UsersService::class);
        return new AuthService($this->captchaServiceMock, new WebsiteUserRepository(), $this->usersServiceMock);
    }

    public function testRegister() {
        Event::fake();
        $attributes = [
            "first_name" => "Ryo",
            "last_name" => "Ryu",
            "address" => "",
            "zipcode" => "",
            "city" => "",
            "state" => "",
            "email" => "test@test.com",
            "phone_number" => "",
            "mobile_number" => "",
            "password" => "12345678",
            "captcha" => "test_token"
        ];
        $service = $this->getConcreteService();

        $this->captchaServiceMock->expects($this->once())->method('validate')->willReturn(true);
        $this->usersServiceMock->expects($this->once())->method('create')->willReturn(TcApiResponseUser::fromData([
            'id' => 1,
            'name' => 'test',
            'email' => 'test@test.com'
        ]));

        $service->register($attributes);
        $this->assertDatabaseHas('website_users', ['email' => 'test@test.com']);
        Event::assertDispatched(Registered::class);
    }

    public function testRegisterWithCaptchaFailure() {
        Event::fake();
        $attributes = [
            "first_name" => "Ryo",
            "last_name" => "Ryu",
            "address" => "",
            "zipcode" => "",
            "city" => "",
            "state" => "",
            "email" => "test@test.com",
            "phone_number" => "",
            "mobile_number" => "",
            "password" => "12345678",
            "captcha" => "test_token"
        ];
        $service = $this->getConcreteService();

        $this->captchaServiceMock->expects($this->once())->method('validate')->willReturn(false);

        $this->expectException(ValidationException::class);
        $service->register($attributes);
    }

    public function testAuthenticate() {
        $user = WebsiteUser::factory()->create(['password' => '12345678']);

        $credentials = [
            'email' => $user->email,
            'password' => '12345678'
        ];

        $service = $this->getConcreteService();
        $token = $service->authenticate($credentials);
        $this->assertTrue((new TokenValidator)->isValid($token));
    }

    public function testAuthenticateWithWrongPassword() {
        $user = WebsiteUser::factory()->create(['password' => Hash::make('12345678')]);

        $credentials = [
            'email' => $user->email,
            'password' => '1234567'
        ];

        $service = $this->getConcreteService();
        $this->expectException(UnauthorizedException::class);
        $service->authenticate($credentials);
    }
}
