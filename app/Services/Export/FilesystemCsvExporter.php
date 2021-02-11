<?php

declare(strict_types=1);

namespace App\Services\Export;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Storage;
use League\Csv\CannotInsertRecord;
use League\Csv\Writer;

/**
 * General purpose export CSV to a `Filesystem` object from query
 */
abstract class FilesystemCsvExporter extends QueryCsvExporter
{
    /**
     * @var null|string
     */
    protected $tmpFileName;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     * @param string $filename
     * @param Builder|EloquentBuilder|null $query
     * @param array|null $headers
     * @param callable|null $lineMapping
     */
    public function __construct(
        Filesystem $filesystem,
        string $filename,
        $query = null,
        ?array $headers = null,
        ?callable $lineMapping = null
    )
    {
        parent::__construct($headers, $lineMapping, $query);

        $this->filename = $filename;
        $this->filesystem = $filesystem;
    }

    /**
     * Crete a fileHandle where a temp csv will be written to
     *
     * @return self
     */
    public function createFile(): self
    {
        $this->csvWriter = Writer::createFromStream($this->writeStream());

        return $this;
    }

    /**
     * Send the temp file to the Filesystem (e.g. `Storage::disk('s3')`)
     *
     * @throws FileNotFoundException
     */
    public function deliver(): void
    {
       $this->filesystem->put($this->filename, $this->readStream());
    }

    /**
     * Chunked query exporter. Overrides the default exporter to accommodate progress
     *
     * @return void
     * @throws CannotInsertRecord
     * @throws FileNotFoundException
     */
    public function export(): void
    {
        // init progress details
        $this->setProgressMax($this->query->count());
        $this->setProgress(0);
        $this->progressIncrement();

        // main loop
        $startTime = time(); // time will be used to set intervals when progress is saved
        $processed = 0;
        $this->query->chunk(1000, function ($lines) use (&$processed, &$startTime) {
            foreach ($lines as $line) {
                $this->write($line);
                $processed++;
            }

            // if it's time to save progress (every after 10 secs)
            if (time() - $startTime > 10) {
                $this->setProgress($processed);
                $this->progressIncrement();
                $startTime = time();
            }
        });

        // last progress update
        $this->setProgress($processed);
        $this->progressIncrement();

        // file has been assembled, now deliver
        $this->deliver();
    }

    /**
     * Assembles the line to write
     *
     * @param array $line a line of data to write
     * @return void
     * @throws CannotInsertRecord
     */
    public function write($line): void
    {
        // for the first write add a header
        if (!$this->headerWritten) {
            $this->csvWriter->insertOne($this->headers);
            $this->headerWritten = true;
        }

        // insert to csv using the supplied line mapper
        $callable = $this->lineMapper;
        $this->csvWriter->insertOne($callable($line));
    }

    /**
     * @return resource
     */
    private function writeStream()
    {
        return fopen($this->touch(), 'wb+');
    }

    /**
     * @return resource
     * @throws FileNotFoundException
     */
    private function readStream()
    {
        return Storage::disk('tmp')->readStream($this->tmpFileName)['stream'];
    }

    /**
     * Initializes the file
     *
     * @return string
     */
    private function touch(): string
    {
        $this->tmpFileName = sprintf('exported-%s-%s.csv', date('Y-m-d-H-i-s'), uniqid('', false));
        Storage::disk('tmp')->put($this->tmpFileName, '');

        return $this->tmpFileName;
    }
}
