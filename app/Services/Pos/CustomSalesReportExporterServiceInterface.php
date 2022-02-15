<?php

declare(strict_types=1);

namespace App\Services\Pos;

use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * Describes the custom sales report exporter
 */
interface CustomSalesReportExporterServiceInterface
{
    /**
     * @param array $params filter params
     * @param Filesystem|null $fs a valid file system
     * @return string filename where the output content were stored
     */
    public function run(array $params, Filesystem $fs = null): string;

    /**
     * @param Filesystem $fs a valid file system
     * @return CustomSalesReportExporterServiceInterface
     */
    public function withFileSystem(Filesystem $fs);
}
