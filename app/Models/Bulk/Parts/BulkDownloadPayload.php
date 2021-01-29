<?php

declare(strict_types=1);

namespace App\Models\Bulk\Parts;

use App\Models\Common\MonitoredJobPayload;
use App\Traits\WithFactory;
use App\Traits\WithGetter;

/**
 * @property-read string $export_file location of the finished file
 * @property-read string $csv_file
 */
class BulkDownloadPayload extends MonitoredJobPayload
{
    use WithGetter;
    use WithFactory;

    /**
     * @var string
     */
    private $export_file;

    public function asArray(): array
    {
        return [
            'export_file' => $this->export_file,
            'csv_file' => $this->csv_file
        ];
    }
}
