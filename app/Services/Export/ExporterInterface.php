<?php

namespace App\Services\Export;

/**
 * Describe a generic file exporter
 */
interface ExporterInterface
{
    /**
     * @return mixed
     */
    public function createFile();

    public function write($line): void;

    public function deliver(): void;

    public function export(): void;
}
