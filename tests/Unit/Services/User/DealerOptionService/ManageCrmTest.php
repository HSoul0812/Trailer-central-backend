<?php

namespace Tests\Unit\Services\User\DealerOptionService;

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
 * class ManageCrmTest
 * @package Tests\Unit\Services\User\DealerOptionService
 *
 * @coversDefaultClass \App\Services\User\DealerOptionsService
 */
class ManageCrmTest extends TestCase
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

    /**
     * @return void
     */
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
     * @covers ::manageCrm
     *
     * @dataProvider validDataProviderForManageCrm
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testActivateCrm($dealerId, $userId, $active)
    {
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
                'active' => $active
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
        $result = $service->manageCrm($dealerId, $active);

        $this->assertTrue($result);
    }

    /**
     * @covers ::activateCrm
     *
     * @dataProvider validDataProviderForManageCrm
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testActivateCrmWithoutCrmUser($dealerId, $userId, $active)
    {
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
                'active' => $active
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

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageCrm($dealerId, $active);

        $this->assertTrue($result);
    }

    /**
     * @covers ::activateCrm
     *
     * @dataProvider validDataProviderForManageCrm
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testActivateCrmWithoutCrmUserRole($dealerId, $userId, $active)
    {
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
                'active' => $active
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

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageCrm($dealerId, $active);

        $this->assertTrue($result);
    }

    /**
     * @covers ::activateCrm
     *
     * @dataProvider validDataProviderForManageCrm
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \Exception
     */
    public function testActivateCrmWithoutNewDealerUser($dealerId, $userId, $active)
    {
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
                'active' => $active
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

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageCrm($dealerId, $active);

        $this->assertTrue($result);
    }

    /**
     * @covers ::manageCrm
     *
     * @dataProvider invalidDataProviderForManageCrm
     *
     * @group DMS
     * @group DMS_DEALER_OPTIONS
     * @throws \TypeError|\Exception
     */
    public function testActivateCrmWithException($dealerId, $active)
    {
        Log::shouldReceive('error');
        $this->expectException(\TypeError::class);

        /** @var DealerOptionsService $service **/
        $service = $this->app->make(DealerOptionsService::class);
        $result = $service->manageCrm($dealerId, $active);

        $this->assertFalse($result);
    }

    /**
     * @return array[]
     */
    public function validDataProviderForManageCrm(): array
    {
        return [
            'Activate CRM' => [
                'dealer_id' => PHP_INT_MAX,
                'user_id' => PHP_INT_MAX - 1,
                'active' => 1
            ],
            'Deactivate CRM' => [
                'dealer_id' => PHP_INT_MAX,
                'user_id' => PHP_INT_MAX - 1,
                'active' => 0
            ],
            'Activate CRM without CRM user' => [
                'dealer_id' => PHP_INT_MAX,
                'user_id' => null,
                'active' => 1
            ]
        ];
    }

    /**
     * @return array[]
     */
    public function invalidDataProviderForManageCrm(): array
    {
        return [
            'Activate CRM without dealer' => [
                'dealer_id' => null,
                'active' => 1
            ],
            'Deactivate CRM without dealer' => [
                'dealer_id' => null,
                'active' => 0
            ]
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
