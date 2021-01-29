<?php

declare(strict_types=1);

namespace App\Models\Bulk\Parts;

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

    public function asArray(): array
    {
        return [
            'import_source' => $this->import_source
        ];
    }
}
