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
            ->passthru();;

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
        $user = $this->getEloquentMock(User::class);

        $user
            ->shouldReceive('getPermissions')
            ->passthru();

        $user
            ->shouldReceive('hasPermission')
            ->passthru();;

        foreach (PermissionsInterface::FEATURES as $feature) {
            foreach (PermissionsInterface::PERMISSION_LEVELS as $permissionLevel) {
                $this->assertFalse($user->hasPermission($feature, $permissionLevel));
            }
        }
    }
}
