<?php

declare(strict_types=1);

namespace App\Services\Pos;

use App\Repositories\Pos\SalesReportRepositoryInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use League\Csv\CannotInsertRecord;
use League\Csv\Writer;
use League\Fractal\Resource\Collection;
use App\Transformers\Pos\Sales\Reports\CustomSalesReportTransformer;

class CustomSalesReportExporterService implements CustomSalesReportExporterServiceInterface
{
    /**
     * @var SalesReportRepositoryInterface
     */
    private $repository;

    /**
     * @var Writer
     */
    private $csvWriter;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var Filesystem
     */
    private $fs;

    public function __construct(SalesReportRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $params filter params
     * @param Filesystem|null $fs a valid file system
     * @return string filename where the output content were stored
     * @throws InvalidArgumentException when filesystem was not provided
     * @throws CannotInsertRecord when cannot insert a line into the output file
     */
    public function run(array $params, Filesystem $fs = null): string
    {
        if ($fs) {
            $this->withFileSystem($fs);
        }

        if (!$this->fs) {
            throw new InvalidArgumentException('Filesystem is required');
        }

        $this->createFile()->export($params);

        return $this->filename;
    }

    /**
     * @param Filesystem $fs
     * @return CustomSalesReportExporterService
     */
    public function withFileSystem(Filesystem $fs): self
    {
        $this->fs = $fs;
        return $this;
    }

    /**
     * Create the file on the Filesystem
     *
     * @return $this
     */
    private function createFile(): self
    {
        $this->csvWriter = Writer::createFromStream($this->writeStream());
        return $this;
    }

    /**
     * Write the content to file
     *
     * @param array $params
     * @return void
     * @throws CannotInsertRecord
     */
    private function export(array $params): void
    {
        $this->writeHeaders([
            'Date',
            'Stock/Sku',
            'Invoice',
            'Title',
            'Model',
            'Place of sale',
            'Qty',
            'Cost',
            'Price',
            'Tax amount',
            'Total amount',
            'Refund',
            'Profit'
        ]);

        foreach ($this->repository->customReportCursor($params) as $line) {
            $this->write([
                $line->date,
                $line->reference,
                $line->doc_num,
                $line->title,
                $line->model,
                $line->type,
                $line->qty,
                $line->cost,
                $line->price,
                $line->taxes_amount,
                $line->taxes_amount + $line->price,
                $line->refund,
                $line->profit
            ]);
        }
    }

    /**
     * Assembles the line to write
     *
     * @param array $line a line of data to write
     * @return void
     * @throws CannotInsertRecord
     */
    private function write(array $line): void
    {
        $this->csvWriter->insertOne($line);
    }

    /**
     * @param array $headers
     * @throws CannotInsertRecord
     */
    private function writeHeaders(array $headers): void
    {
        $this->write($headers);
    }

    /**
     * @return resource
     */
    private function readStream()
    {
        return fopen($this->fs->path($this->filename), 'rb');
    }

    /**
     * @return resource
     */
    private function writeStream()
    {
        $this->filename = sprintf('custom-sales-report-exported-%s-%s.csv', date('Y-m-d-H-i-s'), Str::random(8));

        return fopen($this->fs->path($this->filename), 'wb+');
    }

    public function get(array $params): Collection
    {
        $salesData = $this->repository->customReport($params);

        $salesData = collect($salesData);
        $groupedCollection = $salesData->groupBy('doc_id');
        $quantityByInvoice = [];

        $salesData = $salesData->map(function ($item) use ($groupedCollection, $quantityByInvoice) {
            if (!Arr::exists($quantityByInvoice, $item->doc_id)) {
                $totalItems = $groupedCollection->get($item->doc_id)->count();
                $quantityByInvoice[$item->doc_id] = round(((float) $item->invoice_discount / $totalItems), 2);
            }

            $partDiscount = $quantityByInvoice[$item->doc_id] ?? 0;
            $item->price = round($item->price - $partDiscount, 2);
            $item->profit = round($item->profit - $partDiscount, 2);

            return $item;
        });

        return new Collection(
            $salesData->toArray(),
            new CustomSalesReportTransformer(),
            'data'
        );
    }
}
