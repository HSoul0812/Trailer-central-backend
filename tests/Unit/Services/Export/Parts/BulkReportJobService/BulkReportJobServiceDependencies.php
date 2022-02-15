<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Export\Parts\BulkReportJobService;

use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;
use App\Repositories\Bulk\Parts\BulkReportRepository;
use App\Repositories\Common\MonitoredJobRepository;
use App\Repositories\Dms\StockRepository;
use App\Repositories\Parts\BinRepositoryInterface;
use App\Services\Common\LoggerService;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Mockery;

class BulkReportJobServiceDependencies
{   /**
     * @var BulkReportRepositoryInterface|LegacyMockInterface|MockInterface
     */
    public $bulkReportRepository;

    /**
     * @var BinRepositoryInterface|LegacyMockInterface|MockInterface
     */
    public $stockRepository;

    /**
     * @var LoggerService|LegacyMockInterface|MockInterface
     */
    public $loggerService;

    /**
     * @var MonitoredJobRepository|LegacyMockInterface|MockInterface
     */
    public $jobsRepository;

    public function __construct()
    {
        $this->bulkReportRepository = Mockery::mock(BulkReportRepository::class);
        $this->stockRepository = Mockery::mock(StockRepository::class);
        $this->loggerService = Mockery::mock(LoggerService::class);
        $this->jobsRepository = Mockery::mock(MonitoredJobRepository::class);
    }

    public function getOrderedArguments(): array
    {
        return [$this->bulkReportRepository, $this->stockRepository, $this->loggerService, $this->jobsRepository];
    }
}
