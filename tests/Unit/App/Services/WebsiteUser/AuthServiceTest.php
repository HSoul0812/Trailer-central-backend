<?php

namespace Tests\Unit\App\Services\WebsiteUser;

use App\Models\WebsiteUser\WebsiteUser;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use App\Services\WebsiteUser\AuthService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Common\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Validators\TokenValidator;

class AuthServiceTest extends TestCase
{
    private MockObject $websiteUserRepository;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    private function getConcreteService(): AuthService {
        $this->websiteUserRepository = $this->mockWebsiteUserRepository();
        return new AuthService($this->websiteUserRepository);
    }

    private function mockWebsiteUserRepository(): MockObject {
        return $this->createMock(WebsiteUserRepositoryInterface::class);
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
            "password" => "12345678"
        ];
        $service = $this->getConcreteService();
        $this->websiteUserRepository->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new WebsiteUser()));
        $service->register($attributes);
        Event::assertDispatched(Registered::class);
    }

    public function testAuthenticate() {
        $credentials = [
            'email' => 'test@test.com',
            'password' => '12345678'
        ];
        $service = $this->getConcreteService();
        $this->websiteUserRepository->expects($this->once())
            ->method('get')
            ->will($this->returnValue(new Collection(new WebsiteUser(
                [
                    'email' => $credentials['email'],
                    'password' => Hash::make($credentials['password'])
                ]
            ))));
        $token = $service->authenticate($credentials);
        $this->assertTrue((new TokenValidator)->isValid($token));
    }
}
