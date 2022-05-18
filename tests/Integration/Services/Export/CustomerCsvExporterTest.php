<?php

namespace Tests\Integration\Services\Export;

use App\Services\Export\Favorites\CustomerCsvExporter;
use App\Services\Export\Favorites\CustomerCsvExporterInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Tests\TestCase;

class CustomerCsvExporterTest extends TestCase
{
    /**
     * Test that SUT is properly bound by the application
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForCustomerCsvExporterInterfaceIsWorking(): void
    {
        $concreteService = $this->getConcreteService();

        self::assertInstanceOf(CustomerCsvExporter::class, $concreteService);
    }

    /**
     * @return void
     * @throws BindingResolutionException
     */
    public function testItGeneratesTheDesiredHeadings()
    {
        $headings = <<<HEADINGS
"First Name","Last Name","Phone Number","Email Address","Terms and Conditions Accepted","Count of Favorites","Date Created","Last Login","Last Update"

HEADINGS;
        
        $service = $this->getConcreteService();
        $csv = $service->export(collect([]));

        self::assertSame($headings, $csv);
    }

    /**
     * @return CustomerCsvExporterInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteService(): CustomerCsvExporterInterface
    {
        return $this->app->make(CustomerCsvExporterInterface::class);
    }
}
