<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Parts\Part;
use App\Repositories\Bulk\BulkDownloadRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Services\Export\HasExporterInterface;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Parts csv exporter
 */
class CsvRunnableService implements CsvRunnableServiceInterface, HasExporterInterface
{
    /**
     * @var BulkDownloadRepositoryInterface
     */
    private $bulkRepository;

    /**
     * @var PartRepositoryInterface
     */
    private $partRepository;

    public function __construct(BulkDownloadRepositoryInterface $bulkRepository, PartRepositoryInterface $partRepository)
    {
        $this->partRepository = $partRepository;
        $this->bulkRepository = $bulkRepository;
    }

    /**
     * Run the service
     *
     * @param BulkDownload $job
     * @return mixed|void
     * @throws Exception
     */
    public function run($job)
    {
        // get stream of parts rows from db
        $partsQuery = $this->partRepository->queryAllByDealerId($job->dealer_id);

        $exporter = $this->getExporter($job);
        // prep the exporter
        $exporter->createFile()
            // set the csv headers
            ->setHeaders($this->getHeaders())

            // a line mapper maps the db columns by name to csv column by position
            ->setLineMapper(function ($line) use ($job) {
                return $this->getLineMapper($line);
            })

            // if progress has incremented, save progress
            ->onProgressIncrement(function ($progress) use ($job): bool {
                return $this->bulkRepository->updateProgress($job->token, $progress);
            })

            // set the exporter's source query
            ->setQuery($partsQuery);

        try {
            $this->bulkRepository->updateProgress($job->token, 0);

            // do the export
            $exporter->export();

            $this->bulkRepository->setCompleted($job->token);
        } catch (Exception $exception) {
            $this->bulkRepository->setFailed($job->token, ['message' => "Got exception: " . $exception->getMessage()]);

            throw $exception;
        }
    }

    /**
     * Maps a `Part` data to respective CSV columns
     *
     * @param Part $part
     * @return array
     */
    public function getLineMapper($part): array
    {
        return [
            'Vendor' => $part->vendor ? $part->vendor->name : '',
            'Brand' => $part->brand ? $part->brand->name : '',
            'Type' => $part->type ? $part->type->name : '',
            'Category' => $part->category ? $part->category->name : '',
            'Subcategory' => $part->subcategory,
            'Title' => $part->title,
            'SKU' => $part->sku,
            'Price' => $part->price,
            'Dealer Cost' => $part->dealer_cost,
            'MSRP' => $part->msrp,
            'Weight' => $part->weight,
            'Weight Rating' => $part->weight_rating,
            'Description' => $part->description,
            'Show on website' => $part->show_on_website,
            'Image' => !empty($part->images) ? implode("\n", $part->images->all()) : '',
            'Video Embed Code' => $part->video_embed_code,
            'Qty' => $part->total_qty
        ];
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Vendor',
            'Brand',
            'Type',
            'Category',
            'Subcategory',
            'Title',
            'SKU',
            'Price',
            'Dealer Cost',
            'MSRP',
            'Weight',
            'Weight Rating',
            'Description',
            'Show on website',
            'Image',
            'Video Embed Code',
            'Qty'
        ];
    }

    /**
     * @param BulkDownload $job
     * @return FilesystemCsvExporter
     */
    public function getExporter($job): FilesystemCsvExporter
    {
        return new FilesystemCsvExporter(Storage::disk('partsCsvExports'), $job->payload->export_file);
    }
}
