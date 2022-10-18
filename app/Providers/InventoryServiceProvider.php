<?php

namespace App\Providers;

use App\Repositories\Bulk\Inventory\BulkDownloadRepository;
use App\Repositories\Bulk\Inventory\BulkDownloadRepositoryInterface;
use App\Services\Export\Inventory\Bulk\BulkDownloadJobService;
use App\Services\Export\Inventory\Bulk\BulkDownloadJobServiceInterface;
use App\Services\Export\Inventory\Bulk\BulkPdfJobService;
use App\Services\Export\Inventory\Bulk\BulkPdfJobServiceInterface;
use Illuminate\Support\ServiceProvider;
use App\Services\Import\Inventory\CsvImportServiceInterface;
use App\Services\Import\Inventory\CsvImportService;

/**
 * Class InventoryServiceProvider
 * @package App\Providers
 */
class InventoryServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(CsvImportServiceInterface::class, CsvImportService::class);
        $this->app->bind(BulkDownloadRepositoryInterface::class, BulkDownloadRepository::class);
        $this->app->bind(BulkDownloadJobServiceInterface::class, BulkDownloadJobService::class);
        $this->app->bind(BulkPdfJobServiceInterface::class, BulkPdfJobService::class);
    }
}
