<?php

namespace Tests\Unit\Models\User;

use App\Models\User\DealerUser;
use App\Models\User\Interfaces\PermissionsInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * Test for App\Models\User\DealerUser
 *
 * Class DealerUserTest
 * @package Tests\Unit\Models\User
 *
 * @coversDefaultClass \App\Models\User\DealerUser
 */
class DealerUserTest extends TestCase
{
    /**
     * @covers ::getPermissions
     * @dataProvider permissionProvider
     *
     * @group DMS
     * @group DMS_DEALER_USER
     *
     * @param Collection $permissions
     */
    public function testGetPermissions(Collection $permissions)
    {
        /** @var LegacyMockInterface|DealerUser $dealerUser */
        $dealerUser = $this->getEloquentMock(DealerUser::class);
        $hasMany = \Mockery::mock(HasMany::class)->shouldAllowMockingProtectedMethods();

        $dealerUser
            ->shouldReceive('getPermissions')
            ->passthru();

        $dealerUser
            ->shouldReceive('perms')
            ->once()
            ->with()
            ->andReturn($hasMany);

        $hasMany
            ->shouldReceive('get')
            ->once()
            ->with()
            ->andReturn($permissions);

        $result1 = $dealerUser->getPermissions();
        $result2 = $dealerUser->getPermissions();

        $this->assertInstanceOf(Collection::class, $result1);
        $this->assertFalse($result1->isEmpty());

        $this->assertEquals($result1, $result2);
        $this->assertEquals($permissions, $result1);
    }

    /**
     * @covers ::hasPermission
     * @dataProvider permissionProvider
     *
     * @group DMS
     * @group DMS_DEALER_USER
     *
     * @param Collection $permissions
     */
    public function testHasPermissions(Collection $permissions)
    {
        /** @var LegacyMockInterface|DealerUser $dealerUser */
        $dealerUser = $this->getEloquentMock(DealerUser::class);
        $hasMany = \Mockery::mock(HasMany::class)->shouldAllowMockingProtectedMethods();

        $dealerUser
            ->shouldReceive('getPermissions')
            ->passthru();

        $dealerUser
            ->shouldReceive('hasPermission')
            ->passthru();

        $dealerUser
            ->shouldReceive('perms')
            ->once()
            ->with()
            ->andReturn($hasMany);

        $hasMany
            ->shouldReceive('get')
            ->once()
            ->with()
            ->andReturn($permissions);

        foreach (PermissionsInterface::FEATURES as $feature) {
            foreach (PermissionsInterface::PERMISSION_LEVELS as $permissionLevel) {
                if (
                    ($feature === PermissionsInterface::INVENTORY && $permissionLevel === PermissionsInterface::CAN_SEE_AND_CHANGE_PERMISSION)
                    || ($feature === PermissionsInterface::INTEGRATIONS && $permissionLevel === PermissionsInterface::CAN_SEE_PERMISSION)
                ) {
                    $this->assertTrue($dealerUser->hasPermission($feature, $permissionLevel));
                } else {
                    $this->assertFalse($dealerUser->hasPermission($feature, $permissionLevel));
                }
            }
        }
    }

    /**
     * @covers ::getPermissions
     *
     * @group DMS
     * @group DMS_DEALER_USER
     */
    public function testGetPermissionsWithoutPermissions()
    {
        /** @var LegacyMockInterface|DealerUser $dealerUser */
        $dealerUser = $this->getEloquentMock(DealerUser::class);
        $hasMany = \Mockery::mock(HasMany::class)->shouldAllowMockingProtectedMethods();
        $collection = new Collection();

        $dealerUser
            ->shouldReceive('getPermissions')
            ->passthru();

        $dealerUser
            ->shouldReceive('perms')
            ->once()
            ->with()
            ->andReturn($hasMany);

        $hasMany
            ->shouldReceive('get')
            ->once()
            ->with()
            ->andReturn($collection);

        $result = $dealerUser->getPermissions();
        $result = $dealerUser->getPermissions();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    /**
     * @covers ::hasPermission
     *
     * @group DMS
     * @group DMS_DEALER_USER
     */
    public function testHasPermissionWithoutPermissions()
    {
        /** @var LegacyMockInterface|DealerUser $dealerUser */
        $dealerUser = $this->getEloquentMock(DealerUser::class);
        $hasMany = \Mockery::mock(HasMany::class)->shouldAllowMockingProtectedMethods();
        $collection = new Collection();

        $dealerUser
            ->shouldReceive('getPermissions')
            ->passthru();

        $dealerUser
            ->shouldReceive('hasPermission')
            ->passthru();

        $dealerUser
            ->shouldReceive('perms')
            ->once()
            ->with()
            ->andReturn($hasMany);

        $hasMany
            ->shouldReceive('get')
            ->once()
            ->with()
            ->andReturn($collection);

        foreach (PermissionsInterface::FEATURES as $feature) {
            foreach (PermissionsInterface::PERMISSION_LEVELS as $permissionLevel) {
                $this->assertFalse($dealerUser->hasPermission($feature, $permissionLevel));
            }
        }
    }

    /**
     * @return Collection[][]
     */
    public function permissionProvider(): array
    {
        return [[
            new Collection([
                [
                    'feature' => PermissionsInterface::INVENTORY,
                    'permission_level' => PermissionsInterface::CAN_SEE_AND_CHANGE_PERMISSION
                ],
                [
                    'feature' => PermissionsInterface::INTEGRATIONS,
                    'permission_level' => PermissionsInterface::CAN_SEE_PERMISSION
                ],
            ])
        ]];
    }
}
