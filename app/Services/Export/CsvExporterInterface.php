<?php

declare(strict_types=1);

namespace App\Services\Export;

/**
 * Describe a csv exporter
 */
interface CsvExporterInterface extends ExporterInterface
{
    public const DELIMITER_COMMA = ',';

    public const DELIMITER_TAB = "\t";
}
