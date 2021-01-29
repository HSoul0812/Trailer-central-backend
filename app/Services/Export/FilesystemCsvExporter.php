<?php

namespace App\Services\Export;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use League\Csv\CannotInsertRecord;
use League\Csv\Writer;

/**
 * Class FilesystemCsvExporterService
 *
 * General purpose export CSV to a `Filesystem` object from query
 *
 * @package App\Services\Export\Parts
 */
class FilesystemCsvExporter extends QueryCsvExporter
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
     * FilesystemCsvExporterService constructor.
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
    public function createFile()
    {
        $this->tmpFileName = env('APP_TMP_DIR', '/tmp') . '/temp-csv-' . date('Y-m-d') . '-' . uniqid() . '.csv';
        $this->tmpFileHandle = fopen($this->tmpFileName, 'w+');

        // make a temp file use a league csv writer; fileHandle is called previously
        // TODO see if a temp file can be skipped and data can be streamed directly to Storage::put()
        $this->csvWriter = Writer::createFromStream($this->tmpFileHandle);
        return $this;
    }

    /**
     * Send the temp file to the Filesystem (e.g. `Storage::disk('s3')`)
     */
    public function deliver(): void
    {
        $fr = fopen($this->tmpFileName, 'r');
        $this->filesystem->put($this->filename, $fr);
        fclose($fr);
    }

    /**
     * Chunked query exporter. Overrides the default exporter to accommodate progress
     *
     * @return void
     * @throws CannotInsertRecord
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
}
