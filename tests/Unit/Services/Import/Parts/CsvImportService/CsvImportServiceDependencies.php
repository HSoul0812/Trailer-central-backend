<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Import\Parts\CsvImportService;

use App\Repositories\Bulk\Parts\BulkUploadRepository;
use App\Repositories\Parts\BinRepository;
use App\Repositories\Parts\PartRepository;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Mockery;

class CsvImportServiceDependencies
{   /**
     * @var BulkUploadRepository|LegacyMockInterface|MockInterface
     */
    public $bulkUploadRepository;

    /**
     * @var PartRepository|LegacyMockInterface|MockInterface
     */
    public $partsRepository;

    /**
     * @var BinRepository|LegacyMockInterface|MockInterface
     */
    public $binRepository;

    public function __construct()
    {
        $this->bulkUploadRepository = Mockery::mock(BulkUploadRepository::class);
        $this->partsRepository = Mockery::mock(PartRepository::class);
        $this->binRepository = Mockery::mock(BinRepository::class);
    }

    public function getOrderedArguments(): array
    {
        return [$this->bulkUploadRepository, $this->partsRepository, $this->binRepository];
    }
}
