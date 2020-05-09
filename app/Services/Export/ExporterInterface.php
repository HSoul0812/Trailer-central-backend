<?php


namespace App\Services\Export;

/**
 * Interface ExporterInterface
 *
 * Generic interface for a query exporter
 *
 * @package App\Services\Export
 */
interface ExporterInterface
{
    function createFile();

    function write($line);

    function deliver();

}
