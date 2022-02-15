<?php

declare(strict_types=1);

namespace App\Services\Export;

/**
 * Describe a generic csv file exporter
 */
interface CsvExporterInterface extends ExporterInterface
{
    public const DELIMITER_COMMA = ',';

    public const DELIMITER_TAB = "\t";

    /**
     * @return mixed
     */
    public function createFile();

    public function write($line): void;

    public function deliver(): void;

    /**
     * Maps a `Object` data to respective CSV columns
     *
     * @param mixed $object
     * @return array
     */
    public function getLineMapper($object): array;

    /**
     * Header names to respective CSV columns
     *
     * @return array
     */
    public function getHeaders(): array;
}
