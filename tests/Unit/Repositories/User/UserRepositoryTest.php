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

    const TEST_DEALER_ID = PHP_INT_MAX;
     /**
     * @var array|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    private $userMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->userMock = $this->getEloquentMock(User::class);
        $this->app->instance(User::class, $this->userMock);

        $this->userRepository = $this->app->make(UserRepositoryInterface::class);

        Queue::fake();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @group overlay_images
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
            ->with(self::TEST_DEALER_ID)
            ->andReturn($this->userMock);

        $this->userRepository->updateOverlaySettings(self::TEST_DEALER_ID);

        Queue::assertPushed(GenerateOverlayImageJob::class, $inventories->count());
    }
}