<?php

declare(strict_types=1);

namespace App\Models\Bulk\Inventory;

use App\Models\Common\MonitoredJobPayload;

/**
 * @property-read string $import_source
 */
class BulkUploadPayload extends MonitoredJobPayload
{
    /**
     * @var string
     */
    protected $import_source;

    /**
     * @var string
     */
    protected $csv_file;

    public function asArray(): array
    {
        $data = [
            'import_source' => $this->import_source
        ];

        return !empty($this->csv_file) ? array_merge($data, ['csv_file' => $this->csv_file]) : $data;
    }
}
