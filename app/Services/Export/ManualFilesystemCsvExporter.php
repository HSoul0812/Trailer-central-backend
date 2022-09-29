<?php

namespace App\Services\Export;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Str;
use Storage;

/**
 * A class created to be a replacement of the FilesystemCsvExporter class
 * the FilesystemCsvExporter class requires developer to send it a query
 * object in order to export the query as the CSV data. This class does
 * not deal with any extra stuffs like updating the process, the class user
 * will need to do any extra work outside this class to keep it simple
 */
class ManualFilesystemCsvExporter
{
    /** @var FilesystemAdapter */
    private $filesystem;

    /** @var resource */
    private $tempFileResource;

    /** @var string */
    private $tempFilePath;

    public function __construct(FilesystemAdapter $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Write the fields array as a line
     *
     * @param array $fields
     * @return ManualFilesystemCsvExporter
     */
    public function writeLine(array $fields): ManualFilesystemCsvExporter
    {
        $this->ensureFileIsOpen();

        fputcsv($this->tempFileResource, $fields);

        return $this;
    }

    /**
     * Export the file to the disk
     * @param string $path
     * @return string The full path of the file
     */
    public function export(string $path): string
    {
        $this->ensureFileIsOpen();

        $this->filesystem->putStream($path, $this->tempFileResource);

        return $this->filesystem->path($path);
    }

    /**
     * Internal method, use to make sure that the file is open
     * before trying to access it
     *
     * @return void
     */
    private function ensureFileIsOpen(): void
    {
        if (is_resource($this->tempFileResource)) {
            return;
        }

        $tempFileName = Str::random() . '.csv';

        $this->tempFilePath = Storage::disk('tmp')->path($tempFileName);

        $this->tempFileResource = fopen($this->tempFilePath, 'a+');
    }

    /**
     * We want to close the resource to clear memory whenever
     * we destroy this object
     */
    public function __destruct()
    {
        if (is_resource($this->tempFileResource)) {
            fclose($this->tempFileResource);
            unlink($this->tempFilePath);
        }
    }
}
