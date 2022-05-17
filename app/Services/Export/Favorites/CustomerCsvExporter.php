<?php

namespace App\Services\Export\Favorites;

use App\Transformers\Export\Favorites\CustomerTransformer;
use Illuminate\Support\Collection;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;

class CustomerCsvExporter implements CustomerCsvExporterInterface
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
            'First Name',
            'Last Name',
            'Phone Number',
            'Email Address',
            'Terms and Conditions Accepted',
            'Count of Favorites',
            'Date Created',
            'Last Login',
            'Last Update'
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
            return (new CustomerTransformer())->transform($record);
        })->toArray());
        return $this->writer->toString();
    }
}
