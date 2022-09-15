<?php

namespace Tests\Integration\Services\Dms\Printer;

use App\Services\Dms\Printer\InstructionsServiceInterface;
use App\Services\Dms\Printer\ZPL\InstructionsService;
use App\Repositories\Dms\Printer\SettingsRepositoryInterface;
use App\Repositories\Dms\Printer\SettingsRepository;
use App\Models\CRM\Dms\Printer\Settings;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\database\seeds\Dms\Printer\SettingsSeeder;
use Tests\TestCase;

/**
 * @coversDefaultClass App\Services\Dms\Printer\ZPL\InstructionsService
 */
class InstructionsServiceTest extends TestCase
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
    public function testIoCForInstructionsServiceIsWorking(): void
    {
        $concreteService = $this->getConcreteService();

        self::assertInstanceOf(InstructionsService::class, $concreteService);
    }

    /**
     * @covers ::getPrintInstruction
     *
     * @group DMS
     * @group DMS_PRINTER
     */
    public function testGetPrintInstructionFails(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->seeder->seed();
        $concreteService = $this->getConcreteService();
        $concreteService->getPrintInstruction(-1, 'test', 'test');
    }

    /**
     * @covers ::getPrintInstruction
     *
     * @group DMS
     * @group DMS_PRINTER
     */
    public function testGetPrintInstructionReturnsArray(): void
    {
        $this->seeder->seed();
        $concreteService = $this->getConcreteService();
        $printData = $concreteService->getPrintInstruction($this->seeder->getDealerId(), 'test', 'test');
        $this->assertIsArray($printData);
    }


    /**
     * @return InstructionsServiceInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteService(): InstructionsServiceInterface
    {
        return $this->app->make(InstructionsServiceInterface::class);
    }
}
