<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Export\Parts\BulkCsvDownloadJobService;

use App\Repositories\Bulk\Parts\BulkDownloadRepository;
use App\Repositories\Common\MonitoredJobRepository;
use App\Repositories\Parts\PartRepository;
use App\Services\Common\LoggerService;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Mockery;

class BulkCsvDownloadJobServiceDependencies
{   /**
     * @var BulkDownloadRepository|LegacyMockInterface|MockInterface
     */
    public $bulkDownloadRepository;

    /**
     * @var PartRepository|LegacyMockInterface|MockInterface
     */
    public $partsRepository;

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
        $this->bulkDownloadRepository = Mockery::mock(BulkDownloadRepository::class);
        $this->partsRepository = Mockery::mock(PartRepository::class);
        $this->loggerService = Mockery::mock(LoggerService::class);
        $this->jobsRepository = Mockery::mock(MonitoredJobRepository::class);
    }

    public function getOrderedArguments(): array
    {
        return [$this->bulkDownloadRepository, $this->partsRepository, $this->loggerService, $this->jobsRepository];
    }
}
