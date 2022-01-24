<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\User;

use App\Models\User\DealerPart;
use App\Repositories\User\DealerPartRepository;
use App\Repositories\User\DealerPartRepositoryInterface;
use Tests\database\seeds\User\DealerPartSeeder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class DealerPartRepositoryTest extends TestCase
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

      self::assertInstanceOf(DealerPartRepository::class, $concreteRepository);
  }

  /**
   * @covers ::get
   * @throws BindingResolutionException
   * @throws Exception when Uuid::uuid4()->toString() could not generate a uuid
   */
  public function testUpdateIsWorkingProperly(): void
  {
    $this->seeder->seed();

    $dealerPart = $this->seeder->dealerPart[0];
    
    $dealerPartParams = [
      'dealer_id' => $dealerPart->dealer_id
    ];
  
    // When I call find
    // Then I got a single tracking data
    /** @var DealerPart $dealerPart */
    $repository = $this->getConcreteRepository();
    
    $updatedDealerPart = $repository->update($dealerPartParams);
    
    self::assertSame($updatedDealerPart->dealer_id, $dealerPartParams['dealer_id']);
  }

  public function testCreateIsWorkingProperly(): void
  {
    $this->seeder->seedDealer();

    $dealerPartParams = [
      'dealer_id' => $this->seeder->dealer->dealer_id,
    ];
  
    // When I call find
    // Then I got a single tracking data
    /** @var DealerPart $dealerPart */
    $repository = $this->getConcreteRepository();
    
    $updatedDealerPart = $repository->create($dealerPartParams);
    
    self::assertSame($updatedDealerPart->dealer_id, $dealerPartParams['dealer_id']);
      
  }

  public function setUp(): void
  {
      parent::setUp();

      $this->seeder = new DealerPartSeeder();
  }

  /**
   * @return DealerPartRepositoryInterface
   *
   * @throws BindingResolutionException when there is a problem with resolution
   *                                    of concreted class
   *
   */
  protected function getConcreteRepository(): DealerPartRepositoryInterface
  {
      return $this->app->make(DealerPartRepositoryInterface::class);
  }
  

}