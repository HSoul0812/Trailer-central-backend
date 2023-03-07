<?php

namespace Tests\Unit\Services\User;

use App\Models\User\NewDealerUser;
use App\Models\User\User;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRoleRepositoryInterface;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\User\NewUserRepositoryInterface;
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

    /**
     * @var LegacyMockInterface|NewUserRepositoryInterface
     */
    private $newUserRepository;

    /**
     * @var LegacyMockInterface|NewDealerUserRepositoryInterface
     */
    private $newDealerUserRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->app->instance(UserRepositoryInterface::class, $this->userRepository);

        $this->crmUserRepository = Mockery::mock(CrmUserRepositoryInterface::class);
        $this->app->instance(CrmUserRepositoryInterface::class, $this->crmUserRepository);

        $this->crmUserRoleRepository = Mockery::mock(CrmUserRoleRepositoryInterface::class);
        $this->app->instance(CrmUserRoleRepositoryInterface::class, $this->crmUserRoleRepository);

        $this->newUserRepository = Mockery::mock(NewUserRepositoryInterface::class);
        $this->app->instance(NewUserRepositoryInterface::class, $this->newUserRepository);

        $this->newDealerUserRepository = Mockery::mock(NewDealerUserRepositoryInterface::class);
        $this->app->instance(NewDealerUserRepositoryInterface::class, $this->newDealerUserRepository);
    }

    /**
     * @covers ::activateCrm
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     */
    public function testActivateCrm()
    {
        $dealerId = PHP_INT_MAX;
        $userId = PHP_INT_MAX - 1;

        $user = new \StdClass();
        $crmUser = new \StdClass();
        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);
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
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     */
    public function testActivateCrmWithoutCrmUser()
    {
        $dealerId = PHP_INT_MAX;
        $userId = PHP_INT_MAX - 1;
        $userName = 'test!11';

        $user = new \StdClass();
        $crmUser = null;
        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);
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
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     */
    public function testActivateCrmWithoutCrmUserRole()
    {
        $dealerId = PHP_INT_MAX;
        $userId = PHP_INT_MAX - 1;

        $user = new \StdClass();
        $crmUser = new \StdClass();
        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);
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
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     */
    public function testActivateCrmWithoutNewDealerUser()
    {
        $dealerId = PHP_INT_MAX;
        $userId = PHP_INT_MAX - 1;

        $userName = 'test_user_name';
        $userEmail = 'test_user_email@test.com';

        $user = $this->getEloquentMock(User::class);
        $crmUser = new \StdClass();
        $newDealerUser = $this->getEloquentMock(NewDealerUser::class);
        $newUser = new \StdClass();
        $crmUserRole = new \StdClass();

        $hasOneOrMany = Mockery::mock(\StdClass::class);

        $crmUser->user_id = $userId;
        $newDealerUser->user_id = $userId;

        $user->name = $userName;
        $user->email = $userEmail;
        $user->crmUser = $crmUser;
        $user->newDealerUser = null;

        $newUser->user_id = $userId;

        $this->userRepository
            ->shouldReceive('beginTransaction')
            ->once();

        $this->userRepository
            ->shouldReceive('get')
            ->once()
            ->with(['dealer_id' => $dealerId])
            ->andReturn($user);

        $this->newUserRepository
            ->shouldReceive('create')
            ->once()
            ->with(\Mockery::on(function ($argument) use ($user) {
                return $argument['username'] === $user->name
                    && $argument['email'] === $user->email
                    && is_string($argument['password']);
            }))
            ->andReturn($newUser);

        $this->newDealerUserRepository
            ->shouldReceive('create')
            ->once()
            ->with(\Mockery::on(function ($argument) use ($newUser) {
                return $argument['user_id'] === $newUser->user_id
                    && is_string($argument['salt'])
                    && $argument['auto_import_hide'] === 0
                    && $argument['auto_msrp'] === 0;
            }))
            ->andReturn($newDealerUser);

        $user
            ->shouldReceive('newDealerUser')
            ->once()
            ->andReturn($hasOneOrMany);

        $hasOneOrMany
            ->shouldReceive('save')
            ->once()
            ->with($newDealerUser);

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
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
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
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
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
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
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
