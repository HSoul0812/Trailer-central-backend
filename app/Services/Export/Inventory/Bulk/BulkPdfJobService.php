<?php

declare(strict_types=1);

namespace App\Services\Export\Inventory\Bulk;

use App\Contracts\LoggerServiceInterface;
use App\Models\Bulk\Inventory\BulkDownload;
use App\Repositories\Bulk\Parts\BulkReportRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\Export\FilesystemPdfExporter;
use App\Services\Export\HasExporterInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

/**
 * This is to decouple service code from the job.
 */
class BulkPdfJobService implements BulkPdfJobServiceInterface, HasExporterInterface
{
    /** @var BulkReportRepositoryInterface */
    private $bulkRepository;

    /** @var InventoryRepositoryInterface */
    private $inventoryRepository;

    /** @var LoggerServiceInterface */
    private $logger;

    public function __construct(
        BulkReportRepositoryInterface $bulkRepository,
        InventoryRepositoryInterface  $inventoryRepository,
        LoggerServiceInterface        $logger
    )
    {
        $this->bulkRepository = $bulkRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->logger = $logger;
    }

    /**
     * @throws Throwable
     */
    public function export(BulkDownload $job): void
    {
        // given it should create an HTML file to be printed and some dealers has many inventory
        // so we need to increase the memory limit to avoid the worker dying
        // this has been tested for ~12k inventory units
        ini_set('memory_limit', '980MB');

        try {
            // @todo: the progress calculation should be accurate using a better way
            $this->logger->info(sprintf("[%s:] starting to export the pdf file for the monitored job '%s'", __CLASS__, $job->token));

            $this->bulkRepository->updateProgress($job->token, 0);

            $data = $this->getData($job);

            $this->bulkRepository->updateProgress($job->token, 10);

            // do the export
            $this->getExporter($job)
                ->withView($this->resolveView())
                ->withData([
                    'data' => $data,
                    'orientation' => $job->payload->orientation ?? FilesystemPdfExporter::ORIENTATION_PORTRAIT
                ])
                ->afterRender(function () use ($job) {
                    $this->bulkRepository->updateProgress($job->token, 15);
                })
                ->afterLoadHtml(function () use ($job) {
                    $this->bulkRepository->updateProgress($job->token, 95);
                })
                ->export();

            $this->bulkRepository->setCompleted($job->token);

            $this->logger->info(sprintf("[%s:] process to export the pdf file for the monitored job '%s' was completed", __CLASS__, $job->token));
        } catch (Throwable $exception) {
            $this->bulkRepository->setFailed($job->token, ['message' => "Got exception: {$exception->getMessage()}"]);
            $this->logger->error(sprintf('[%s:] got exception: %s', __CLASS__, $exception->getMessage()), $exception->getTrace());

            throw $exception;
        }
    }

    /**
     * @param BulkDownload $job
     * @return resource|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function readStream(BulkDownload $job)
    {
        return $this->getExporter($job)->readStream();
    }

    protected function resolveView(): View
    {
        return view('prints.pdf.inventory.list');
    }

    protected function getData(BulkDownload $job): LazyCollection
    {
        $filters = ['dealer_id' => $job->dealer_id] + array_filter($job->payload->filters);

        /**
         * Filter only floored inventories to pay
         * https://crm.trailercentral.com/accounting/floorplan-payment
         */
        $pullOnlyFloorPlanned = (bool) ($filters['only_floorplanned'] ?? false);

        return $pullOnlyFloorPlanned
            ? $this->inventoryRepository->getFloorplannedInventoryAsCursor($filters)
            : $this->inventoryRepository->getAllAsCursor($filters);
    }

    /**
     * @param BulkDownload $job
     * @return FilesystemPdfExporter
     * @throws InvalidArgumentException when the job has a payload without a filename
     */
    public function getExporter($job): FilesystemPdfExporter
    {
        if ($job->payload->filename === '' || $job->payload->filename === null) {
            throw new InvalidArgumentException('This job has a payload without a filename');
        }

        $exporter = new FilesystemPdfExporter(Storage::disk('s3'), $job->payload->filename);

        $exporter->engine()
            ->setOption('header-font-size', '6')
            ->setOption('header-left', now()->format('m/d/Y g:i A'))
            ->setOption('footer-right', 'Page [page] of [toPage]')
            ->setOption('footer-font-size', '6')
            ->setOption('orientation', $job->payload->orientation ?? FilesystemPdfExporter::ORIENTATION_PORTRAIT);

        return $exporter;
    }
}
