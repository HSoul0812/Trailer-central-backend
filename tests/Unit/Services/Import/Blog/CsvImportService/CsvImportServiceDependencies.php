<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Import\Blog\CsvImportService;

use App\Repositories\Website\Blog\BulkUploadRepository;
use App\Repositories\Website\Blog\PostRepository;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Mockery;

class CsvImportServiceDependencies
{   /**
     * @var BulkUploadRepository|LegacyMockInterface|MockInterface
     */
    public $bulkUploadRepository;

    /**
     * @var PostRepository|LegacyMockInterface|MockInterface
     */
    public $postRepository;


    public function __construct()
    {
        $this->bulkUploadRepository = Mockery::mock(BulkUploadRepository::class);
        $this->postRepository = Mockery::mock(PostRepository::class);
    }

    public function getOrderedArguments(): array
    {
        return [$this->bulkUploadRepository, $this->postRepository];
    }
}
