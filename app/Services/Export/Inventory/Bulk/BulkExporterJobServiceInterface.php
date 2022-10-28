<?php

namespace App\Services\Export\Inventory\Bulk;

use App\Models\Bulk\Inventory\BulkDownload;

interface BulkExporterJobServiceInterface
{
    public function export(BulkDownload $job): void;

    public function readStream(BulkDownload $job);
}
