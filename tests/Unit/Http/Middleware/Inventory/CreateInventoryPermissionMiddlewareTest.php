<?php

namespace Tests\Unit\Http\Middleware\Inventory;

use App\Http\Middleware\Inventory\CreateInventoryPermissionMiddleware;
use App\Models\User\DealerUser;
use App\Models\User\Interfaces\PermissionsInterface as Permissions;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tests\TestCase;

/**
 * Test for App\Http\Middleware\Inventory\CreateInventoryPermissionMiddleware
 *
 * Class CreateInventoryPermissionMiddlewareTest
 * @package Tests\Unit\Http\Middleware\Inventory
 *
 * @coversDefaultClass \App\Http\Middleware\Inventory\CreateInventoryPermissionMiddleware
 */
class CreateInventoryPermissionMiddlewareTest extends TestCase
{
    /**
     * @covers ::handle
     *
     * @group DMS
     * @group DMS_INVENTORY_PERMISSION
     */
    public function testHandleWithUser()
    {
        $request = Mockery::mock(Request::class);

        $next = $this->getCallback();

        $user = $this->getEloquentMock(User::class);

        $request
            ->shouldReceive('user')
            ->with()
            ->once()
            ->andReturn($user);

        $middleware = new CreateInventoryPermissionMiddleware();

        /** @var Response $result */
        $result = $middleware->handle($request, $next->getClosure());

        $this->assertCalled($next);

        $this->assertNull($result);
    }

    /**
     * @covers ::handle
     *
     * @group DMS
     * @group DMS_INVENTORY_PERMISSION
     */
    public function testHandleWithSuperAdminPermission()
    {
        $request = Mockery::mock(Request::class);

        $next = $this->getCallback();

        $dealerUser = $this->getEloquentMock(DealerUser::class);

        $request
            ->shouldReceive('user')
            ->with()
            ->once()
            ->andReturn($dealerUser);

        $dealerUser
            ->shouldReceive('hasPermission')
            ->times(3)
            ->with(Permissions::INVENTORY, Permissions::SUPER_ADMIN_PERMISSION)
            ->andReturn(true);

        $middleware = new CreateInventoryPermissionMiddleware();

        /** @var Response $result */
        $result = $middleware->handle($request, $next->getClosure());

        $this->assertCalled($next);

        $this->assertNull($result);
    }

    /**
     * @covers ::handle
     *
     * @group DMS
     * @group DMS_INVENTORY_PERMISSION
     */
    public function testHandleWithoutSuperAdminPermission()
    {
        $request = Mockery::mock(Request::class);

        $notSuperAdminField = 'test_field';
        $superAdminField1 = CreateInventoryPermissionMiddleware::SUPER_ADMIN_FIELDS[0];
        $superAdminField2 = CreateInventoryPermissionMiddleware::SUPER_ADMIN_FIELDS[1];

        $requestSuperAdminFields = [
            $superAdminField1 => 1,
            $superAdminField2 => 2,
            $notSuperAdminField => 3
        ];

        $request->request = new ParameterBag($requestSuperAdminFields);

        $next = $this->getCallback();

        $dealerUser = $this->getEloquentMock(DealerUser::class);

        $request
            ->shouldReceive('user')
            ->with()
            ->once()
            ->andReturn($dealerUser);

        $request
            ->shouldReceive('offsetUnset')
            ->twice()
            ->andReturn(true);

        $dealerUser
            ->shouldReceive('hasPermission')
            ->twice()
            ->with(Permissions::INVENTORY, Permissions::SUPER_ADMIN_PERMISSION)
            ->andReturn(false);

        $dealerUser
            ->shouldReceive('hasPermission')
            ->twice()
            ->with(Permissions::INVENTORY, Permissions::CAN_SEE_AND_CHANGE_PERMISSION)
            ->andReturn(true);

        $dealerUser
            ->shouldReceive('hasPermission')
            ->once()
            ->with(Permissions::INVENTORY, Permissions::SUPER_ADMIN_PERMISSION)
            ->andReturn(false);

        $middleware = new CreateInventoryPermissionMiddleware();

        /** @var Response $result */
        $result = $middleware->handle($request, $next->getClosure());

        $requestParams = $request->request->all();

        $this->assertCount(1, $requestParams);
        $this->assertArrayHasKey($notSuperAdminField, $requestParams);
        $this->assertArrayNotHasKey($superAdminField1, $requestParams);
        $this->assertArrayNotHasKey($superAdminField2, $requestParams);

        $this->assertCalled($next);

        $this->assertNull($result);
    }


    /**
     * @covers ::handle
     *
     * @group DMS
     * @group DMS_INVENTORY_PERMISSION
     */
    public function testHandleWithoutPermission()
    {
        $request = Mockery::mock(Request::class);

        $next = $this->getCallback();

        $dealerUser = $this->getEloquentMock(DealerUser::class);

        $request
            ->shouldReceive('user')
            ->with()
            ->once()
            ->andReturn($dealerUser);

        $dealerUser
            ->shouldReceive('hasPermission')
            ->once()
            ->with(Permissions::INVENTORY, Permissions::SUPER_ADMIN_PERMISSION)
            ->andReturn(false);

        $dealerUser
            ->shouldReceive('hasPermission')
            ->once()
            ->with(Permissions::INVENTORY, Permissions::CAN_SEE_AND_CHANGE_PERMISSION)
            ->andReturn(false);

        $dealerUser
            ->shouldReceive('hasPermission')
            ->once()
            ->with(Permissions::INVENTORY, Permissions::CAN_SEE_AND_CHANGE_IMAGES_PERMISSION)
            ->andReturn(false);

        $dealerUser
            ->shouldReceive('hasPermission')
            ->once()
            ->with(Permissions::INVENTORY, Permissions::CAN_SEE_PERMISSION)
            ->andReturn(false);

        $middleware = new CreateInventoryPermissionMiddleware();

        /** @var Response $result */
        $result = $middleware->handle($request, $next->getClosure());

        $this->assertNotCalled($next);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame('Invalid access token.', $result->getContent());
        $this->assertSame(403, $result->getStatusCode());
    }

    /**
     * @covers ::handle
     *
     * @group DMS
     * @group DMS_INVENTORY_PERMISSION
     */
    public function testHandleWithoutUser()
    {
        $request = Mockery::mock(Request::class);

        $next = $this->getCallback();

        $request
            ->shouldReceive('user')
            ->with()
            ->once()
            ->andReturn(null);

        $middleware = new CreateInventoryPermissionMiddleware();

        /** @var Response $result */
        $result = $middleware->handle($request, $next->getClosure());

        $this->assertNotCalled($next);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertSame('Invalid access token.', $result->getContent());
        $this->assertSame(403, $result->getStatusCode());
    }
}
