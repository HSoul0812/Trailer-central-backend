<?php

namespace Tests\Unit\Services\User;

use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRoleRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\DealerOptionsService;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * Test for App\Services\User\DealerOptionsService
 *
 * Class InventoryServiceTest
 * @package Tests\Unit\Services\Inventory
 *
 * @coversDefaultClass \App\Services\User\DealerOptionsService
 */
class DealerOptionsServiceTest extends TestCase
{
    /**
     * @var LegacyMockInterface|UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var LegacyMockInterface|CrmUserRepositoryInterface
     */
    private $crmUserRepository;

    /**
     * @var LegacyMockInterface|CrmUserRoleRepositoryInterface
     */
    private $crmUserRoleRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepository);

        $this->crmUserRepository = Mockery::mock(CrmUserRepositoryInterface::class);
        $this->app->instance(CrmUserRepositoryInterface::class, $this->crmUserRepository);

        $this->crmUserRoleRepository = Mockery::mock(CrmUserRoleRepositoryInterface::class);
        $this->app->instance(CrmUserRoleRepositoryInterface::class, $this->crmUserRoleRepository);
    }

    /**
     * @covers ::activateCrm
     */
    public function testActivateCrm()
    {
        $dealerId = PHP_INT_MAX;
        $userId = PHP_INT_MAX - 1;

        $user = new \StdClass();
        $crmUser = new \StdClass();
        $newDealerUser = new \StdClass();
        $crmUserRole = new \StdClass();

        $crmUser->user_id = $userId;
        $newDealerUser->user_id = $userId;

        $user->crmUser = $crmUser;
        $user->newDealerUser = $newDealerUser;

        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once();

        $this->userRepository
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($user);

        $this->crmUserRepository
            ->shouldReceive('update')
            ->once()
            ->with([
                'user_id' => $crmUser->user_id,
                'active' => 1
            ])
            ->andReturn($user);

        $this->crmUserRepository
            ->shouldReceive('create')
            ->never();

        $this->crmUserRoleRepository
            ->shouldReceive('get')
            ->once()
            ->with(['user_id' => $newDealerUser->user_id])
            ->andReturn($crmUserRole);

        $this->crmUserRoleRepository
            ->shouldReceive('create')
            ->never();

        $this->userRepository
            ->shouldReceive('commitTransaction')
            ->once();

        Log::shouldReceive('info');

        /** @var DealerOptionsService $service */
        $service = $this->app->make(DealerOptionsService::class);

        $result = $service->activateCrm($dealerId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::activateCrm
     */
    public function testActivateCrmWithoutCrmUser()
    {
        $dealerId = PHP_INT_MAX;
        $userId = PHP_INT_MAX - 1;
        $userName = 'test!11';

        $user = new \StdClass();
        $crmUser = null;
        $newDealerUser = new \StdClass();
        $crmUserRole = new \StdClass();

        $newDealerUser->user_id = $userId;

        $user->crmUser = $crmUser;
        $user->newDealerUser = $newDealerUser;

        $user->name = $userName;

        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once();

        $this->userRepository
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($user);

        $this->crmUserRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'user_id' => $newDealerUser->user_id,
                'logo' => '',
                'first_name' => '',
                'last_name' => '',
                'display_name' => '',
                'dealer_name' => $user->name,
                'active' => 1
            ])
            ->andReturn($user);

        $this->crmUserRepository
            ->shouldReceive('update')
            ->never();

        $this->crmUserRoleRepository
            ->shouldReceive('get')
            ->once()
            ->with(['user_id' => $newDealerUser->user_id])
            ->andReturn($crmUserRole);

        $this->crmUserRoleRepository
            ->shouldReceive('create')
            ->never();

        $this->userRepository
            ->shouldReceive('commitTransaction')
            ->once();

        Log::shouldReceive('info');

        /** @var DealerOptionsService $service */
        $service = $this->app->make(DealerOptionsService::class);

        $result = $service->activateCrm($dealerId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::activateCrm
     */
    public function testActivateCrmWithoutCrmUserRole()
    {
        $dealerId = PHP_INT_MAX;
        $userId = PHP_INT_MAX - 1;

        $user = new \StdClass();
        $crmUser = new \StdClass();
        $newDealerUser = new \StdClass();
        $crmUserRole = null;

        $crmUser->user_id = $userId;
        $newDealerUser->user_id = $userId;

        $user->crmUser = $crmUser;
        $user->newDealerUser = $newDealerUser;

        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once();

        $this->userRepository
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($user);

        $this->crmUserRepository
            ->shouldReceive('update')
            ->once()
            ->with([
                'user_id' => $crmUser->user_id,
                'active' => 1
            ])
            ->andReturn($user);

        $this->crmUserRepository
            ->shouldReceive('create')
            ->never();

        $this->crmUserRoleRepository
            ->shouldReceive('get')
            ->once()
            ->with(['user_id' => $newDealerUser->user_id])
            ->andReturn($crmUserRole);

        $this->crmUserRoleRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'user_id' => $newDealerUser->user_id,
                'role_id' => 'user'
            ])
            ->andReturn($crmUserRole);

        $this->userRepository
            ->shouldReceive('commitTransaction')
            ->once();

        Log::shouldReceive('info');

        /** @var DealerOptionsService $service */
        $service = $this->app->make(DealerOptionsService::class);

        $result = $service->activateCrm($dealerId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::activateCrm
     */
    public function testActivateCrmWithException()
    {
        $dealerId = PHP_INT_MAX;

        $user = null;

        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once();

        $this->userRepository
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($user);

        $this->crmUserRepository
            ->shouldReceive('update')
            ->never();

        $this->crmUserRepository
            ->shouldReceive('create')
            ->never();

        $this->crmUserRoleRepository
            ->shouldReceive('get')
            ->never();

        $this->crmUserRoleRepository
            ->shouldReceive('create')
            ->never();

        $this->userRepository
            ->shouldReceive('commitTransaction')
            ->never();

        $this->userRepository
            ->shouldReceive('rollbackTransaction')
            ->once();

        Log::shouldReceive('error');

        /** @var DealerOptionsService $service */
        $service = $this->app->make(DealerOptionsService::class);

        $result = $service->activateCrm($dealerId);

        $this->assertFalse($result);
    }

    /**
     * @covers ::deactivateCrm
     */
    public function testDeactivateCrm()
    {
        $dealerId = PHP_INT_MAX;
        $userId = PHP_INT_MAX - 1;

        $user = new \StdClass();
        $newDealerUser = new \StdClass();

        $newDealerUser->user_id = $userId;

        $user->newDealerUser = $newDealerUser;

        $this->userRepository
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($user);

        $this->crmUserRepository
            ->shouldReceive('update')
            ->once()
            ->with([
                'user_id' => $newDealerUser->user_id,
                'active' => false
            ])
            ->andReturn($user);

        Log::shouldReceive('info');

        /** @var DealerOptionsService $service */
        $service = $this->app->make(DealerOptionsService::class);

        $result = $service->deactivateCrm($dealerId);

        $this->assertTrue($result);
    }

    /**
     * @covers ::deactivateCrm
     */
    public function testDeactivateCrmWithException()
    {
        $dealerId = PHP_INT_MAX;

        $user = null;

        $this->userRepository
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($user);

        $this->crmUserRepository
            ->shouldReceive('update')
            ->never();

        Log::shouldReceive('error');

        /** @var DealerOptionsService $service */
        $service = $this->app->make(DealerOptionsService::class);

        $result = $service->deactivateCrm($dealerId);

        $this->assertFalse($result);
    }
}
