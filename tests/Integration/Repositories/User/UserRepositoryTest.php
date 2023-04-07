<?php

namespace Tests\Integration\Repositories\User;

use App\Models\Inventory\Inventory;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Integration\Repositories\Inventory\InventoryRepositoryTest;
use App\Models\User\User;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\database\seeds\User\SettingsSeeder;
use Tests\TestCase;

class UserRepositoryTest  extends TestCase
{
    use WithFaker;

    /** @var SettingsSeeder */
    private $seeder;

    /** @var UserRepositoryInterface  */
    private $repository;

    /** @var User */
    private $dealer;

    /**
     * Test that repository is properly bound by the application
     *
     * @typeOfTest IntegrationTestCase
     *
     * @group DMS
     * @group DMS_USER
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    public function testRepositoryInterfaceIsBound(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(UserRepository::class, $concreteRepository);
    }

    /**
     * @group DMS
     * @group DMS_USER
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function testCreate() {
        $user = $this->getConcreteRepository()->create([
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'testtest'
        ]);
        $this->assertDatabaseHas('dealer', [
            'name' => 'Test',
            'email' => 'test@test.com'
        ]);
        $this->assertInstanceOf(User::class, $user);
        $user->delete();
    }

    /**
     * @group DMS
     * @group DMS_USER
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function testGet() {
        $this->getConcreteRepository()->create([
            'name' => 'Test',
            'email' => 'test123@test.com',
            'password' => 'testtest'
        ]);
        $user = $this->getConcreteRepository()->getByEmail('test123@test.com');
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test123@test.com', $user->email);
        $user->delete();
    }

    /**
     * Test that SUT will not touch `overlay_updated_at` due there was not any overlay configuration changed
     *
     * @group DW
     * @group DW_INVENTORY
     *
     * @covers ::UpdateOverlaySettings
     */
    public function testUpdateOverlaySettingsByNotChangingOverlayUpdatedAt()
    {
        $this->dealer = $this->repository->create(array_merge(
            [
                'name' => 'Test',
                'email' => $this->faker->email,
                'password' => 'testtest'
            ],
            InventoryRepositoryTest::OVERLAY_DEFAULT_CONFIGURATION
        ));

        $performedChanges = $this->repository->updateOverlaySettings(
            $this->dealer->dealer_id,
            ['overlay_enabled' => Inventory::OVERLAY_ENABLED_PRIMARY]
        );

        $this->assertSame(Inventory::OVERLAY_ENABLED_PRIMARY, $performedChanges['overlay_enabled']);

        $this->dealer = $this->repository->getByEmail($this->dealer->email);

        $this->assertNull($this->dealer->overlay_updated_at);
    }

    /**
     * Test that SUT will touch `overlay_updated_at` due there was overlay configuration changed
     *
     * @group DW
     * @group DW_INVENTORY
     *
     * @covers ::UpdateOverlaySettings
     */
    public function testUpdateOverlaySettingsByChangingOverlayUpdatedAt()
    {
        $this->dealer = $this->repository->create(array_merge(
            [
                'name' => 'Test',
                'email' => $this->faker->email,
                'password' => 'testtest'
            ],
            InventoryRepositoryTest::OVERLAY_DEFAULT_CONFIGURATION
        ));

        $performedChanges = $this->repository->updateOverlaySettings(
            $this->dealer->dealer_id,
            [
                'overlay_enabled' => Inventory::OVERLAY_ENABLED_PRIMARY,
                'overlay_logo' => 'logo2.png',
            ]
        );

        $this->assertSame(Inventory::OVERLAY_ENABLED_PRIMARY, $performedChanges['overlay_enabled']);

        $this->dealer = $this->repository->getByEmail($this->dealer->email);

        $this->assertNotNull($this->dealer->overlay_updated_at);
    }

    /**
     * @return UserRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): UserRepositoryInterface
    {
        return $this->app->make(UserRepositoryInterface::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getConcreteRepository();
    }

    protected function tearDown(): void
    {
        if ($this->dealer) {
            $this->dealer->delete();
            $this->dealer = null;
        }

        parent::tearDown();
    }
}
