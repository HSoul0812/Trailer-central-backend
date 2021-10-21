<?php
namespace Tests\Unit\Services\Website;

use App\Models\Website\User\WebsiteUser;
use App\Models\Website\User\WebsiteUserFavoriteInventory;
use App\Repositories\Website\WebsiteUserFavoriteInventoryRepository;
use App\Repositories\Website\WebsiteUserFavoriteInventoryRepositoryInterface;
use App\Repositories\Website\WebsiteUserRepository;
use App\Repositories\Website\WebsiteUserRepositoryInterface;
use App\Services\Website\WebsiteUserServiceInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

        $this->expectException(HttpException::class);

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

    public function testAddUserInventories() {
        $websiteUserId = 1;
        $inventoryIds = [1, 2, 3];

        $inventory1 = new WebsiteUserFavoriteInventory([
            'website_user_id' => $websiteUserId,
            'inventory_id' => $inventoryIds[0]
        ]);
        $inventory2 = new WebsiteUserFavoriteInventory([
            'website_user_id' => $websiteUserId,
            'inventory_id' => $inventoryIds[1]
        ]);
        $inventory3 = new WebsiteUserFavoriteInventory([
            'website_user_id' => $websiteUserId,
            'inventory_id' => $inventoryIds[2]
        ]);

        $websiteUserFavoriteInventoryRepository = Mockery::mock(WebsiteUserFavoriteInventoryRepository::class);
        $this->app->instance(
            WebsiteUserFavoriteInventoryRepositoryInterface::class,
            $websiteUserFavoriteInventoryRepository
        );
        $websiteUserFavoriteInventoryRepository
            ->shouldReceive('create')
            ->andReturn(
                $inventory1,
                $inventory2,
                $inventory3
            );
        $websiteUserService = app()->make(WebsiteUserServiceInterface::class);
        $inventories = $websiteUserService->addUserInventories($websiteUserId, $inventoryIds);
        $this->assertEquals($inventory1, $inventories[0]);
        $this->assertEquals($inventory2, $inventories[1]);
        $this->assertEquals($inventory3, $inventories[2]);
    }

}
