<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Services\Export\FilesystemCsvExporter as GenericFilesystemCsvExporter;
use League\Csv\Writer;

/**
 * General purpose export CSV to a `Filesystem` object from query
 */
class FilesystemCsvExporter extends GenericFilesystemCsvExporter
{
    /**
     * Crete a fileHandle where a temp csv for parts will be written to
     *
     * @return self
     */
    public function createFile(): self
    {
        $this->tmpFileName = env('APP_TMP_DIR', '/tmp') . '/part-csv-' . date('Y-m-d') . '-' . uniqid() . '.csv';
        $this->tmpFileHandle = fopen($this->tmpFileName, 'w+');

        // make a temp file use a league csv writer; fileHandle is called previously
        // TODO see if a temp file can be skipped and data can be streamed directly to Storage::put()
        $this->csvWriter = Writer::createFromStream($this->tmpFileHandle);

        return $this;
    }
}
