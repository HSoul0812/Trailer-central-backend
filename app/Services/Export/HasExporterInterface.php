<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Models\Common\MonitoredJob;

interface HasExporterInterface
{
    /**
     * @param MonitoredJob $job
     * @return ExporterInterface
     */
    public function getExporter($job);
}
