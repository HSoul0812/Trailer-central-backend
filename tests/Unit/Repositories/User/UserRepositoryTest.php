<?php

namespace Unit\Repositories\User;

use App\Models\User\User;
use App\Models\Inventory\Inventory;
use App\Repositories\User\UserRepositoryInterface;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Collection;

class UserRepositoryTest extends TestCase {

     /**
     * @var array|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $userMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->userMock = $this->getEloquentMock(User::class);
        $this->app->instance(User::class, $this->userMock);
        $this->userMock->dealer_id = 1;

        Queue::fake();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @group Marketing
     * @group Marketing_Overlays
     */
    public function testUpdateOverlaySettings()
    {
        $inventories = new Collection([
            $this->getEloquentMock(Inventory::class),
            $this->getEloquentMock(Inventory::class),
            $this->getEloquentMock(Inventory::class)
        ]);

        $this->userMock->shouldReceive('getAttributes')
            ->once()->with('inventories')
            ->andReturn($inventories);

        $this->userMock->shouldReceive('save')
            ->once()->with();

        $this->userMock->shouldReceive('findOrFail')
            ->once()
            ->with($this->userMock->dealer_id)
            ->andReturn($this->userMock);

        $userRepo = $this->app->make(UserRepositoryInterface::class);
        $result = $userRepo->updateOverlaySettings($this->userMock->dealer_id);

        //Queue::assertPushed(GenerateOverlayImageJob::class, $inventories->count());
        $this->assertEquals($result->dealer_id, $this->userMock->dealer_id);
    }
}