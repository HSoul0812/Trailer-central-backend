<?php

declare(strict_types=1);

namespace App\Providers;

use App\Jobs\Bulk\Parts\FinancialReportCsvExportJob;
use App\Jobs\Bulk\Parts\FinancialReportExportJob;
use App\Jobs\Dms\ServiceOrder\ServiceTechnicianExportJob;
use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;
use App\Services\Export\Parts\BulkReportCsvJobServiceInterface;
use App\Services\Export\Parts\BulkReportJobServiceInterface;
use App\Services\Dms\ServiceOrder\BulkCsvTechnicianReportServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Injects Dependencies that are required by the Jobs.
 */
class JobServiceProvider extends ServiceProvider
{
    /**
     * Registers the handle method parameters to the jobs.
     */
    public function register(): void
    {
        $this->app->bindMethod(FinancialReportExportJob::class . '@handle', function (FinancialReportExportJob $job): void {
            $job->handle(
                $this->app->make(BulkReportRepositoryInterface::class),
                $this->app->make(BulkReportJobServiceInterface::class)
            );
        });
        
        $this->app->bindMethod(ServiceTechnicianExportJob::class . '@handle', function (ServiceTechnicianExportJob $job): void {
            $job->handle(
                $this->app->make(BulkReportRepositoryInterface::class),
                $this->app->make(BulkCsvTechnicianReportServiceInterface::class)
            );
        });

        $this->app->bindMethod(FinancialReportCsvExportJob::class . '@handle', function (FinancialReportCsvExportJob $job): void {
            $job->handle(
                $this->app->make(BulkReportRepositoryInterface::class),
                $this->app->make(BulkReportCsvJobServiceInterface::class)
            );
        });
    }
}
