<?php

declare(strict_types=1);

namespace App\Models\Bulk\Parts;

use App\Models\Common\MonitoredJobPayload;

/**
 * @property-read string $export_file location of the finished file
 * @property-read string $csv_file
 */
class BulkDownloadPayload extends MonitoredJobPayload
{
    /**
     * @var string
     */
    protected $export_file;

    public function asArray(): array
    {
        return [
            'export_file' => $this->export_file
        ];
    }
}
