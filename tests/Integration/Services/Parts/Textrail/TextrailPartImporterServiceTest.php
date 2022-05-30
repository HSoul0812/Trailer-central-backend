<?php

namespace Tests\Integration\Services\Parts\Textrail;

use App\Services\Parts\Textrail\TextrailPartService;
use App\Services\Parts\Textrail\TextrailPartImporterService;
use App\Repositories\Parts\Textrail\PartRepository;
use App\Repositories\Parts\Textrail\BrandRepositoryInterface;
use App\Repositories\Parts\Textrail\TypeRepositoryInterface;
use App\Repositories\Parts\Textrail\ManufacturerRepositoryInterface;
use App\Repositories\Parts\Textrail\CategoryRepositoryInterface;
use App\Repositories\Parts\Textrail\ImageRepositoryInterface;
use App\Transformers\Parts\Textrail\TextrailPartsTransformer;
use Tests\Integration\Services\Ecommerce\DataProvider\Providers\TextrailMagentoSandbox;
use League\Fractal\Manager;
use Tests\TestCase;

class TextrailPartImporterServiceTest extends TestCase
{
    public function testTextrailImporterServiceCreate(): void
    {

      $service = new TextrailPartImporterService(app()->make(PartRepository::class),
                  new TextrailPartService(new TextrailMagentoSandbox()),
                  app()->make(CategoryRepositoryInterface::class),
                  app()->make(BrandRepositoryInterface::class),
                  app()->make(ManufacturerRepositoryInterface::class),
                  app()->make(TypeRepositoryInterface::class),
                  app()->make(ImageRepositoryInterface::class),
                  app()->make(TextrailPartsTransformer::class),
                  app()->make(Manager::class));

      $service->run();
      $dataProvider = new TextrailMagentoSandbox();
      $partRepository = $this->app->make(PartRepository::class);
      $getOnePart = $dataProvider->getAllParts()[0];

      $getDatabasePart = $partRepository->getBySku($getOnePart->sku);

      self::assertDatabaseHas('textrail_parts', [
        'sku' => $getOnePart->sku
      ]);

    }

    public function testTextrailImporterServiceUpdate(): void
    {
      $service = new TextrailPartImporterService(app()->make(PartRepository::class),
                  new TextrailPartService(new TextrailMagentoSandbox()),
                  app()->make(CategoryRepositoryInterface::class),
                  app()->make(BrandRepositoryInterface::class),
                  app()->make(ManufacturerRepositoryInterface::class),
                  app()->make(TypeRepositoryInterface::class),
                  app()->make(ImageRepositoryInterface::class),
                  app()->make(TextrailPartsTransformer::class),
                  app()->make(Manager::class));

      //run the service to get the parts to the database
      $service->run();
      $dataProvider = new TextrailMagentoSandbox();
      $partRepository = $this->app->make(PartRepository::class);
      $getOnePart = $dataProvider->getAllParts()[0];

      // updated one part to other price
      $getDatabasePart = $partRepository->getBySku($getOnePart->sku);

      $partRepository->update(['id' => $getDatabasePart->id, 'price' => 144]);

      // run again to check the importer service update back the price
      $service->run();
      $getDatabasePartAgain = $partRepository->getBySku($getOnePart->sku);
      // check the price is the same after import again
      self::assertSame($getOnePart->price, (int)$getDatabasePartAgain->price);
    }
}
