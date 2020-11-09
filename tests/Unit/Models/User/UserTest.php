<?php

namespace Tests\Unit\Models\User;

use App\Models\User\Interfaces\PermissionsInterface;
use App\Models\User\User;
use Illuminate\Support\Collection;
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
     */
    public function testGetPermissions()
    {
        $user = $this->getEloquentMock(User::class);

        $result = $user->getPermissions();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertTrue($result->isEmpty());
    }

    /**
     * @covers ::hasPermission
     */
    public function testHasPermission()
    {
        $user = $this->getEloquentMock(User::class);

        foreach (PermissionsInterface::FEATURES as $feature) {
            foreach (PermissionsInterface::PERMISSION_LEVELS as $permissionLevel) {
                $this->assertFalse($user->hasPermission($feature, $permissionLevel));
            }
        }
    }
}
