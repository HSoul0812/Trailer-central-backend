<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Integration\CVR\CvrFileService;

use App\Repositories\Common\MonitoredJobRepository;
use App\Repositories\Integration\CVR\CvrFileRepository;
use App\Services\Dms\CVR\CVRGeneratorService;
use App\Services\Common\LoggerService;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Mockery;

class CvrFileServiceDependencies
{   /**
     * @var CvrFileRepository|LegacyMockInterface|MockInterface
     */
    public $fileRepository;

    /**
     * @var LoggerService|LegacyMockInterface|MockInterface
     */
    public $loggerService;

    /**
     * @var MonitoredJobRepository|LegacyMockInterface|MockInterface
     */
    public $jobsRepository;
    
    /**
     * @var CVRGeneratorService|LegacyMockInterface|MockInterface
     */
    public $cvrGeneratorService;

    public function __construct()
    {
        $this->fileRepository = Mockery::mock(CvrFileRepository::class);
        $this->loggerService = Mockery::mock(LoggerService::class);
        $this->jobsRepository = Mockery::mock(MonitoredJobRepository::class);
        $this->cvrGeneratorService = Mockery::mock(CVRGeneratorService::class);
    }

    public function getOrderedArguments(): array
    {
        return [$this->fileRepository, $this->loggerService, $this->jobsRepository, $this->cvrGeneratorService];
    }
}
