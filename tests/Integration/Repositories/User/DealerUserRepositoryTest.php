<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\User;

use App\Repositories\User\DealerUser;
use App\Models\User\DealerUserPermission;
use App\Repositories\User\DealerUserRepository;
use App\Repositories\User\DealerUserRepositoryInterface;
use Tests\database\seeds\User\DealerUserSeeder;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class DealerUserRepositoryTest extends TestCase
{
  /**
   * Test that SUT is properly bound by the application
   *
   * @group DMS
   * @group DMS_DEALER_USER
   *
   * @throws BindingResolutionException when there is a problem with resolution
   *                                    of concreted class
   * @note IntegrationTestCase
   */
  public function testIoCForTheRepositoryInterfaceIsWorking(): void
  {
      $concreteRepository = $this->getConcreteRepository();

      self::assertInstanceOf(DealerUserRepository::class, $concreteRepository);
  }

  /**
   * @covers ::get
   *
   * @group DMS
   * @group DMS_DEALER_USER
   *
   * @throws BindingResolutionException
   * @throws Exception when Uuid::uuid4()->toString() could not generate a uuid
   */
  public function testUpdateWithUserPermissionIsWorkingProperly(): void
  {
    $this->seeder->seed();

    $dealerId = $this->seeder->dealer->getKey();

    $dealerUserParams = [
      'dealer_id' => $dealerId,
      'email' => 'email@test12.com',
      'password' => 'test123',
      'user_permissions' => [
        [
          'feature' => 'ecommerce',
          'permission_level' => 'can_see'
        ]
      ]
    ];

    // When I call find
    // Then I got a single tracking data
    /** @var DealerUser $dealerUser */
    $repository = $this->getConcreteRepository();

    $newDealerUser = $repository->create($dealerUserParams);

    $updateDealerUserParams = [
      'dealer_user_id' => $newDealerUser->getKey(),
      'email' => 'email@test12.com',
      'password' => 'test123',
      'user_permissions' => [
        [
          'feature' => 'ecommerce',
          'permission_level' => 'can_see_and_change'
        ]
      ]
    ];

    $updatedDealerUser = $repository->update($updateDealerUserParams);
    $dealerUserPermission = DealerUserPermission::where([
      'dealer_user_id' => $updatedDealerUser->getKey(),
      'feature' => 'ecommerce',
    ])->first();

    self::assertSame($newDealerUser->dealer_user_id, $updateDealerUserParams['dealer_user_id']);
    self::assertSame($newDealerUser->email, $updateDealerUserParams['email']);

    $firstPermission = Arr::first($updateDealerUserParams['user_permissions']);
    self::assertSame($dealerUserPermission->feature, $firstPermission['feature']);
    self::assertSame($dealerUserPermission->permission_level, $firstPermission['permission_level']);
    $this->seeder->cleanUp();
  }

    /**
     * @group DMS
     * @group DMS_DEALER_USER
     *
     * @return void
     * @throws BindingResolutionException
     */
  public function testCreateWithPermissionsIsWorkingProperly(): void
  {

      $this->seeder->seed();

      $dealerId = $this->seeder->dealer->getKey();

      $dealerUserParams = [
        'dealer_id' => $dealerId,
        'email' => 'email@test12.com',
        'password' => 'test123',
        'user_permissions' => [
          [
            'feature' => 'ecommerce',
            'permission_level' => 'can_see'
          ]
        ]
      ];

      // When I call find
      // Then I got a single tracking data
      /** @var DealerUser $dealerUser */
      $repository = $this->getConcreteRepository();

      $newDealerUser = $repository->create($dealerUserParams);
      $dealerUserPermission = DealerUserPermission::where([
        'dealer_user_id' => $newDealerUser->getKey(),
        'feature' => 'ecommerce'
      ])->first();

      self::assertSame($newDealerUser->dealer_id, $dealerUserParams['dealer_id']);
      self::assertSame($newDealerUser->email, $dealerUserParams['email']);

      $firstPermission = Arr::first($dealerUserParams['user_permissions']);
      self::assertSame($dealerUserPermission->feature, $firstPermission['feature']);
      self::assertSame($dealerUserPermission->permission_level, $firstPermission['permission_level']);
      $this->seeder->cleanUp();
  }

  public function setUp(): void
  {
      parent::setUp();

      $this->seeder = new DealerUserSeeder();
  }

  /**
   * @return DealerUserRepositoryInterface
   *
   * @throws BindingResolutionException when there is a problem with resolution
   *                                    of concreted class
   *
   */
  protected function getConcreteRepository(): DealerUserRepositoryInterface
  {
      return $this->app->make(DealerUserRepositoryInterface::class);
  }
}
