<?php

namespace App\Services\Export\Favorites;

use App\Transformers\Export\Favorites\InventoryTransformer;
use Illuminate\Support\Collection;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;

class InventoryCsvExporter implements InventoryCsvExporterInterface
{
    /**
     * @var Writer
     */
    private $writer;

    public function __construct()
    {
        $this->writer = Writer::createFromString();
    }

    /**
     * @throws CannotInsertRecord
     */
    private function setCsvHeaders()
    {
        $this->writer->insertOne([
            'Stock #',
            'Vin',
            'Location',
            'Condition',
            'Type',
            'Category',
            'Title',
            'Year',
            'Manufacturer',
            'Status',
            'MSRP',
            'Model',
            'Price',
            'Sales Price',
            'Hidden Price'
        ]);
    }

    /**
     * @param Collection $data
     * @return string
     * @throws CannotInsertRecord
     * @throws Exception
     */
    public function export(Collection $data): string
    {
        $this->setCsvHeaders();
        $this->writer->insertAll($data->map(function ($record) {
            return (new InventoryTransformer())->transform($record);
        })->toArray());
        return $this->writer->toString();
    }
}
