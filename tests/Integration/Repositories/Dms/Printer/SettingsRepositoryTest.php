<?php

namespace Tests\Unit\Repositories\Dms\Printer;

use App\Repositories\Dms\Printer\SettingsRepositoryInterface;
use App\Repositories\Dms\Printer\SettingsRepository;
use App\Models\CRM\Dms\Printer\Settings;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\database\seeds\Dms\Printer\SettingsSeeder;
use Tests\TestCase;

/**
 * @coversDefaultClass App\Repositories\Dms\Printer\SettingsRepository
 */
class SettingsRepositoryTest extends TestCase
{    
    /**
     * @var SettingsSeeder
     */
    private $seeder;
    
    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new SettingsSeeder();
    }
    
    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }
    
    /**
     * Test that SUT is properly bound by the application
     *
     * @group DMS
     * @group DMS_PRINTER
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    public function testIoCForSettingsRepositoryIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(SettingsRepository::class, $concreteRepository);
    } 
    
    /**
     * @covers ::getByDealerId
     *
     * @group DMS
     * @group DMS_PRINTER
     */
    public function testGetByDealerIdReturnsDealerRecord(): void 
    {
        $this->seeder->seed();
        $concreteRepository = $this->getConcreteRepository();
        
        $printerSettings = $concreteRepository->getByDealerId($this->seeder->getDealerId());
        
        $this->assertEquals($this->seeder->getDealerId(), $printerSettings->dealer_id);
    }
    
    /**
     * @covers ::getByDealerId
     *
     * @group DMS
     * @group DMS_PRINTER
     */
    public function testGetByDealerIdReturnsPrinterSettings(): void 
    {
        $this->seeder->seed();
        $concreteRepository = $this->getConcreteRepository();
        
        $printerSettings = $concreteRepository->getByDealerId($this->seeder->getDealerId());
        
        self::assertInstanceOf(Settings::class, $printerSettings);
    }
    
    /**
     * @covers ::getByDealerId
     *
     * @group DMS
     * @group DMS_PRINTER
     */
    public function testGetByDealerIdFails(): void 
    {
        $this->expectException(ModelNotFoundException::class);
        
        $this->seeder->seed();
        $concreteRepository = $this->getConcreteRepository();
        
        $printerSettings = $concreteRepository->getByDealerId(-1);
    }
    
    /**
     * @return SettingsRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): SettingsRepositoryInterface
    {
        return $this->app->make(SettingsRepositoryInterface::class);
    }
}
