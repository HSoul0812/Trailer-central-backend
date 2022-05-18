<?php

namespace Tests\Integration\Services\Export;

use App\Services\Export\Favorites\CustomerCsvExporter;
use App\Services\Export\Favorites\CustomerCsvExporterInterface;
use App\Services\Export\Favorites\InventoryCsvExporter;
use App\Services\Export\Favorites\InventoryCsvExporterInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;

class InventoryCsvExporterTest extends TestCase
{
    /**
     * Test that SUT is properly bound by the application
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForInventoryCsvExporterInterfaceIsWorking(): void
    {
        $concreteService = $this->getConcreteService();

        self::assertInstanceOf(InventoryCsvExporter::class, $concreteService);
    }

    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function testItGeneratesTheDesiredHeadings()
    {
        $headings = <<<HEADINGS
"Stock #",Vin,Location,Condition,Type,Category,Title,Year,Manufacturer,Status,MSRP,Model,Price,"Sales Price","Hidden Price"

HEADINGS;

        $service = $this->getConcreteService();
        $csv = $service->export(collect([]));

        self::assertSame($headings, $csv);
    }

    /**
     * @return InventoryCsvExporterInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteService(): InventoryCsvExporterInterface
    {
        return $this->app->make(InventoryCsvExporterInterface::class);
    }
}
