<?php

namespace Tests\Integration\Services\Parts\Textrail;

use App\Services\Parts\Textrail\TextrailPartService;
use App\Services\Parts\Textrail\TextrailPartServiceInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;
use stdClass;

class TextrailPartServiceTest extends TestCase
{

    public function testIoCForTheServiceIsWorking(): void
    {
        $concreteService = $this->getConcreteService();

        self::assertInstanceOf(TextrailPartService::class, $concreteService);
    }

    public function testgetAllPartsWithGetTextrailTotalPartsCount()
    {
      $getAllParts = $this->getConcreteService()->getAllParts();
      $getTextrailTotalPartsCount = $this->getConcreteService()->getTextrailTotalPartsCount();

      self::assertSame($getTextrailTotalPartsCount, count($getAllParts));
    }

    public function testgetTextrailCategory()
    {
      $getCategory = $this->getConcreteService()->getTextrailCategory(1);
      self::assertInstanceOf(stdClass::class, $getCategory);
    }

    public function testgetTextrailBrands()
    {
      $getBrands = $this->getConcreteService()->getTextrailBrands();

      self::assertIsArray($getBrands);
    }

    public function testgetTextrailManufacturers()
    {
      $getManufacturers = $this->getConcreteService()->getTextrailManufacturers();

      self::assertIsArray($getManufacturers);
    }

    /**
     * @return TextrailPartServiceInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteService(): TextrailPartServiceInterface
    {
        return $this->app->make(TextrailPartServiceInterface::class);
    }
}
