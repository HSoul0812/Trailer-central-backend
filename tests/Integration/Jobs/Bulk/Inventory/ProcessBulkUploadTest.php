<?php

namespace Tests\Integration\Jobs\Bulk\Inventory;

use App\Jobs\Inventory\GenerateOverlayAndReIndexInventoriesByDealersJob as InventoryBackgroundWorkFlowByDealerJob;
use App\Services\Import\Inventory\CsvImportServiceInterface;
use App\Services\Inventory\InventoryServiceInterface;
use App\Jobs\Bulk\Inventory\ProcessBulkUpload;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Bulk\Inventory\BulkUpload;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;
use Mockery;

/**
 * @covers \App\Jobs\Bulk\Inventory\ProcessBulkUpload::handle
 * @group DW
 * @group DW_BULK
 * @group DW_BULK_INVENTORY
 * @group DW_BULK_UPLOAD_INVENTORY
 * @group DW_ELASTICSEARCH
 * @group DW_INVENTORY
 * @group INTEGRATION
 * @group INTEGRATION_BULK
 * @group INTEGRATION_BULK_UPLOAD_INVENTORY
 */
class ProcessBulkUploadTest extends TestCase
{
    use WithFaker;

    /** @var BulkUpload */
    public $model;

    /** @var CsvImportServiceInterface|(CsvImportServiceInterface&Mockery\LegacyMockInterface)|(CsvImportServiceInterface&Mockery\MockInterface)|Mockery\LegacyMockInterface|Mockery\MockInterface */
    private $importerService;

    /** @var InventoryServiceInterface */
    private $inventoryService;

    public function testWillCatchExceptionsAndLogItsMessage(): void
    {
        $expectedExceptionMessage = 'Some odd exception happened';

        $spy = Log::spy();

        $job = new class($this->model->id, $expectedExceptionMessage) extends ProcessBulkUpload {
            /** @var string */
            private $anExceptionMessage;

            public function __construct(int $bulkId, string $anExceptionMessage)
            {
                parent::__construct($bulkId);

                $this->anExceptionMessage = $anExceptionMessage;
            }

            protected function findModel(int $int): BulkUpload
            {
                throw new RuntimeException($this->anExceptionMessage);
            }
        };


        $spy->allows('info')->once()->with('Starting inventory bulk upload');
        $spy->allows('error')->once()->with($expectedExceptionMessage);

        $job->handle($this->importerService, $this->inventoryService);
    }

    public function testWillDispatchTheExpectedJobs(): void
    {
        $spy = Log::spy();

        $job = new class($this->model->id, $this->model) extends ProcessBulkUpload {
            /** @var BulkUpload */
            private $model;

            public function __construct(int $bulkId, BulkUpload $model)
            {
                parent::__construct($bulkId);

                $this->model = $model;
            }

            protected function findModel(int $int): BulkUpload
            {
                return $this->model;
            }
        };

        $spy->allows('info')->once()->with('Starting inventory bulk upload');

        $spy->allows('info')
            ->once()
            ->with(sprintf('Inventory bulk upload %d was processed', $this->model->id));

        $this->importerService->expects('setBulkUpload')->with($this->model);
        $this->importerService->allows('run')->once()->withNoArgs();

        $job->handle($this->importerService, $this->inventoryService);

        Bus::assertDispatchedTimes(InventoryBackgroundWorkFlowByDealerJob::class, 1);
    }

    public function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        $this->importerService = Mockery::mock(CsvImportServiceInterface::class)->shouldAllowMockingMethod('run');
        $this->inventoryService = app(InventoryServiceInterface::class);

        $this->model = factory(BulkUpload::class)->make([
            'id' => $this->faker->numberBetween(1, 300),
            'dealer_id' => $this->faker->numberBetween(1, 300)
        ]);
    }
}
