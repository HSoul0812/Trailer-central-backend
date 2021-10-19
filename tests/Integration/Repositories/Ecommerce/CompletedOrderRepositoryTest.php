<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Repositories\Ecommerce\CompletedOrderRepository;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use Tests\database\seeds\Ecommerce\CompletedOrderSeeder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class CompletedOrderRepositoryTest extends TestCase
{
  /**
   * Test that SUT is properly bound by the application
   *
   * @throws BindingResolutionException when there is a problem with resolution
   *                                    of concreted class
   * @note IntegrationTestCase
   */
  public function testIoCForTheRepositoryInterfaceIsWorking(): void
  {
      $concreteRepository = $this->getConcreteRepository();

      self::assertInstanceOf(CompletedOrderRepository::class, $concreteRepository);
  }

  public function testIndexWithFilterIsWorkingProperly(): void
  {
      
      $this->seeder->seed();

      $completedOrderParams = [
        'status' => 'dropshipped'
      ];

      // When I call find
      // Then I got a single tracking data
      /** @var CompletedOrder $completedOrder */
      $repository = $this->getConcreteRepository();
      
      $completedOrders = $repository->getAll($completedOrderParams);

      self::assertSame($completedOrders[0]->status, $completedOrderParams['status']);
  }

  public function setUp(): void
  {
      parent::setUp();

      $this->seeder = new CompletedOrderSeeder();
  }

  /**
   * @return CompletedOrderRepositoryInterface
   *
   * @throws BindingResolutionException when there is a problem with resolution
   *                                    of concreted class
   *
   */
  protected function getConcreteRepository(): CompletedOrderRepositoryInterface
  {
      return $this->app->make(CompletedOrderRepositoryInterface::class);
  }
  

}