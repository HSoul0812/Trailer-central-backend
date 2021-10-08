<?php
namespace Tests\Unit\Services\Website;

use App\Models\Website\User\WebsiteUser;
use App\Repositories\Website\WebsiteUserRepository;
use App\Repositories\Website\WebsiteUserRepositoryInterface;
use App\Services\Website\WebsiteUserServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Mockery;
use Tests\TestCase;

/**
 * Test for App\Services\Website\WebsiteUserService
 * Class WebsiteUserServiceTest
 * @package Tests\Unit\Services\Website
 *
 */
class WebsiteUserServiceTest extends TestCase {

    public function setUp(): void
    {
        parent::setUp();
    }

    public function testLoginUserSuccess(): void {
        $params = [
            'email' => 'email@email.com',
            'password' => '12345',
            'website_id' => 123,
        ];
        $websiteUser = new WebsiteUser($params);
        $websiteUserRepository = Mockery::mock(WebsiteUserRepository::class);
        $this->app->instance(WebsiteUserRepositoryInterface::class, $websiteUserRepository);
        $websiteUserRepository
            ->shouldReceive('get')
            ->once()
            ->andReturn($websiteUser);
        $websiteUserService = $this->app->make(WebsiteUserServiceInterface::class);
        $result = $websiteUserService->loginUser($params);
        $this->assertEquals($result->email, $params['email']);
        $this->assertTrue($result->checkPassword($params['password']));
    }

    public function testLoginUserFail(): void {
        $params = [
            'email' => 'email@email.com',
            'password' => '12345',
            'website_id' => 123,
        ];
        $websiteUser = new WebsiteUser(
            array_replace($params, [
                'password' => '1234'
            ])
        );

        $websiteUserRepository = Mockery::mock(WebsiteUserRepository::class);
        $websiteUserRepository
            ->shouldReceive('get')
            ->once()
            ->andReturn($websiteUser);

        $this->app->instance(WebsiteUserRepositoryInterface::class, $websiteUserRepository);

        $this->expectException(AuthenticationException::class);

        $websiteUserService = $this->app->make(WebsiteUserServiceInterface::class);
        $websiteUserService->loginUser($params);

    }

    public function testCreateUser(): void {
        $params = [
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'email' => 'email@email.com',
            'password' => '12345',
            'website_id' => 123,
            'token' => 'token12345'
        ];
        $websiteUser = new WebsiteUser($params);
        $websiteUserRepository = Mockery::mock(WebsiteUserRepository::class);
        $this->app->instance(WebsiteUserRepositoryInterface::class, $websiteUserRepository);
        $websiteUserRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($websiteUser);
        $websiteUserService = $this->app->make(WebsiteUserServiceInterface::class);
        $result = $websiteUserService->createUser($params);
        $this->assertEquals($result->email, $params['email']);
        $this->assertTrue($result->checkPassword($params['password']));
    }

}
