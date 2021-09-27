<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Website\Config;

use App\Models\Website\Config\WebsiteConfig;
use App\Repositories\Website\Config\WebsiteConfigRepository;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use Tests\database\seeds\Website\Config\WebsiteConfigSeeder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;
use Tests\Integration\WithMySqlConstraintViolationsParser;

class WebsiteConfigRepositoryTest extends TestCase
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

      self::assertInstanceOf(WebsiteConfigRepository::class, $concreteRepository);
  }

  /**
   * @covers ::get
   * @throws BindingResolutionException
   * @throws Exception when Uuid::uuid4()->toString() could not generate a uuid
   */
  public function testUpdateIsWorkingProperly(): void
  {
      $this->seeder->seed();

      $websiteConfig = $this->seeder->websiteConfig[0];
      
      $websiteParams = [
        'id' => $websiteConfig->id,
        'website_id' => $websiteConfig->website_id,
        'key' => $websiteConfig->key,
        'value' => $websiteConfig->value
      ];
    
      // When I call find
      // Then I got a single tracking data
      /** @var WebsiteConfig $websiteconfig */
      $repository = $this->getConcreteRepository();
      
      $updatedWebsiteConfig = $repository->update($websiteParams);
      
      self::assertSame($updatedWebsiteConfig->website_id, $websiteParams['website_id']);
      self::assertSame($updatedWebsiteConfig->key, $websiteParams['key']);
      self::assertSame($updatedWebsiteConfig->value, $websiteParams['value']);
  }

  public function testCreateIsWorkingProperly(): void
  {

      $this->seeder->seed();

      $websiteConfigParams = [
        'website_id' => $this->seeder->website->id,
        'key' => WebsiteConfig::ECOMMERCE_KEY_ENABLE,
        'value' => 1
      ];
    
      // When I call find
      // Then I got a single tracking data
      /** @var WebsiteConfig $websiteconfig */
      $repository = $this->getConcreteRepository();
      
      $updatedWebsiteConfig = $repository->create($websiteConfigParams);
      
      self::assertSame($updatedWebsiteConfig->website_id, $websiteConfigParams['website_id']);
      self::assertSame($updatedWebsiteConfig->key, $websiteConfigParams['key']);
      self::assertSame($updatedWebsiteConfig->value, $websiteConfigParams['value']);
  }

  public function setUp(): void
  {
      parent::setUp();

      $this->seeder = new WebsiteConfigSeeder();
  }

  /**
   * @return WebsiteConfigRepositoryInterface
   *
   * @throws BindingResolutionException when there is a problem with resolution
   *                                    of concreted class
   *
   */
  protected function getConcreteRepository(): WebsiteConfigRepositoryInterface
  {
      return $this->app->make(WebsiteConfigRepositoryInterface::class);
  }
  

}