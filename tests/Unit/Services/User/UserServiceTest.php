<?php

namespace Tests\Unit\Services\User;

use App\Models\User\User;
use App\Services\User\UserService;
use Tests\TestCase;

class UserServiceTest extends TestCase
{

    public function testSetAdminPasswd()
    {
        /** @var UserService $service */
        $service = app(UserService::class);
        $service->setAdminPasswd(1001, '1234');

        $user = User::find(1001);
        $this->assertSame(sha1('1234'), $user->admin_passwd);
    }
}
