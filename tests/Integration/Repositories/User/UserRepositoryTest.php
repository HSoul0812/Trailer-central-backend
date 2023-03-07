<?php

namespace Integration\Repositories\User;

use App\Models\User\User;
use App\Repositories\User\SettingsRepository;
use App\Repositories\User\SettingsRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\database\seeds\User\SettingsSeeder;
use Tests\TestCase;

class UserRepositoryTest  extends TestCase
{
    /**
     * @var SettingsSeeder
     */
    private $seeder;

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
}
