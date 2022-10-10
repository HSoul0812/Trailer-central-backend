<?php

namespace Tests\Unit\Models\User;

use App\Models\User\Interfaces\PermissionsInterface;
use App\Models\User\User;
use Illuminate\Support\Collection;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * Test for App\Models\User\User
 *
 * Class UserTest
 *
 * @package Tests\Unit\Models\User
 *
 * @coversDefaultClass \App\Models\User\User
 */
class UserTest extends TestCase
{
    /**
     * @covers ::getPermissions
     *
     * @group DMS
     * @group DMS_USER
     */
    public function testGetPermissions()
    {
        /** @var LegacyMockInterface|User $user */
        $user = $this->getEloquentMock(User::class);

        $user
            ->shouldReceive('getPermissions')
            ->passthru();

        $result = $user->getPermissions();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    /**
     * @covers ::hasPermission
     *
     * @group DMS
     * @group DMS_USER
     */
    public function testHasPermission()
    {
        /** @var LegacyMockInterface|User $user */
        $user = $this->getEloquentMock(User::class)->makePartial();

        $user->shouldReceive('hasCrmPermission')->passthru();
        $user->shouldReceive('getDealerId')->andReturn($this->getTestDealerId());

        // Check permission for CRM feature
        $this->assertTrue(
            $user->hasPermission(PermissionsInterface::CRM, PermissionsInterface::SUPER_ADMIN_PERMISSION)
        );

        // Check permission for Accounts feature
        $this->assertTrue(
            $user->hasPermission(PermissionsInterface::ACCOUNTS, PermissionsInterface::CAN_SEE_AND_CHANGE_PERMISSION)
        );
    }

    /**
     * @covers ::hasPermission
     *
     * @group DMS
     * @group DMS_USER
     */
    public function testHasPermissionInactiveCrmUser()
    {
        /** @var LegacyMockInterface|User $user */
        $user = $this->getEloquentMock(User::class)->makePartial();

        $user->shouldReceive('hasCrmPermission')->andReturn(false);
        $user->shouldReceive('getDealerId')->andReturn($this->getTestDealerId());

        // Check permission for CRM feature
        $this->assertFalse(
            $user->hasPermission(PermissionsInterface::CRM, PermissionsInterface::SUPER_ADMIN_PERMISSION)
        );

        // Check permission for POS feature
        $this->assertTrue(
            $user->hasPermission(PermissionsInterface::POS, PermissionsInterface::CANNOT_SEE_PERMISSION)
        );
    }
}
