<?php

namespace App\Domains\DealerExports;

use App\Models\User\User;
use Exception;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Csv\Writer;
use Storage;
use Illuminate\Support\Facades\Log;
use App\Models\DealerExport;

/**
 * Class BaseExportAction
 *
 * @package App\Domains\DealerExports
 */
abstract class BaseExportAction
{
    const S3_EXPORT_PATH = 'exports/{dealer}/{entity}.csv';
    const EXPORT_FILE_DIRECTORY = 'exports/{dealer}';

    /** @var FilesystemAdapter */
    protected $storage;

    /** @var User */
    protected $dealer;

    /** @var FilesystemAdapter */
    protected $tmpStorage;

    /**
     * @var string<string,string>[]
     */
    protected $headers = [];

    /** @var string */
    protected $filename;

    /** @var string */
    protected $entity;

    /** @var string */
    protected $directory;

    /** @var Collection<int, object> */
    protected $rows;

    /** @var Writer */
    protected $writer;

    public function __construct(User $dealer)
    {
        $this->storage = Storage::disk('s3');

        $this->rows = collect([]);

        $this->dealer = $dealer;
    }

    protected function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    protected function setEntity(string $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    protected function setFilename()
    {
        $this->filename = str_replace(['{dealer}', '{entity}'], [$this->dealer->dealer_id, $this->entity], self::S3_EXPORT_PATH);

        $this->directory = str_replace(['{dealer}'], [$this->dealer->dealer_id], self::EXPORT_FILE_DIRECTORY);

        return $this;
    }

    protected function initiateWriter()
    {
        $this->tmpStorage = Storage::disk('tmp');

        $this->tmpStorage->makeDirectory($this->directory);

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

        $this->tmpStorage->delete($this->filename);
    }

    public function export()
    {
        (new ExportStartAction($this->dealer, $this->entity))->execute();

        try {
            $this->setFileName()
                ->initiateWriter()
                ->writeHeader()
                ->fetchResults()
                ->writeResults()
                ->generateFile()
                ->uploadFile();
        } catch (Exception $e) {
            Log::channel('dealer-export')->error('Error occurred while exporting ' . $this->entity . ' Line #: ' . $e->getLine());
            Log::channel('dealer-export')->error(
                'Exception: ' . $e->getMessage()
            );

            DealerExport::query()
                ->where('dealer_id', $this->dealer->dealer_id)
                ->where('entity_type', $this->entity)
                ->update([
                    'status' => DealerExport::STATUS_ERROR,
                ]);

            return;
        }

        (new ExportFinishedAction($this->dealer, $this->entity, $this->storage->url($this->filename)))->execute();
    }

    public function transformRow($row)
    {
        $headers = array_keys($this->headers);

        return array_map(function (string $header) use ($row) {
            return object_get($row, $header);
        }, $headers);
    }

    protected function writeResults()
    {
        $this->writeData([$this, 'transformRow']);

        return $this;
    }

    protected function fetchResults()
    {
        $this->rows = $this->getQuery()->get();

        return $this;
    }
}
