<?php

namespace App\Domains\DealerExports;

use Illuminate\Filesystem\FilesystemAdapter;
use League\Csv\Writer;
use Illuminate\Database\Query\Builder;

class BaseExportAction
{
    const S3_EXPORT_PATH = 'exports/{dealer}/{entity}.csv';

    /** @var FilesystemAdapter */
    protected $storage;

    /** @var FilesystemAdapter */
    protected $tmpStorage;

    /**
     * @var string<string,string>[]
     */
    protected $headers = [];

    /** @var string */
    protected $filename;

    /** @var Collection<int, object> */
    protected $rows;

    /** @var Writer */
    protected $writer;

    public function __construct()
    {
        $this->storage = Storage::disk('s3');

        $this->rows = collect([]);
    }

    protected function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    protected function setFilename($entity)
    {
        $this->filename = str_replace([], [$this->dealer->dealer_id, $entity], self::S3_EXPORT_PATH);

        return $this;
    }

    protected function InitiateWriter()
    {
        $this->tmpStorage = Storage::disk('tmp');

        $this->writer = Writer::createFromPath($this->tmpStorage->path($this->filename), 'w+');

        return $this;
    }

    protected function writeHeader()
    {
        $this->writer->insertOne(array_values($this->headers));

        return $this;
    }

    protected function writeData($transformCallback)
    {
        foreach ($this->rows as $row) {
            $csvRow = $transformCallback($row);

            $this->writer->insertOne($csvRow);
        }

        return $this;
    }

    protected function generateFile()
    {
        $this->tmpStorage->put($this->filename, $this->writer->toString());

        return $this;
    }

    protected function uploadFile()
    {
        $result = $this->storage->putStream($this->filename, $this->tmpStorage->readStream($this->filename));

        throw_if(!$result, new \Exception("Can't upload CSV file to S3, please check configuration variables."));
    }
}
