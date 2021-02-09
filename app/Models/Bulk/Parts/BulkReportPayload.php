<?php

declare(strict_types=1);

namespace App\Models\Bulk\Parts;

use App\Models\Common\MonitoredJobPayload;

/**
 * @property-read string $type the type of report
 * @property-read string $filename location of the finished file
 */
class BulkReportPayload extends MonitoredJobPayload
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $filename;

    public function asArray(): array
    {
        return [
            'type' => $this->type,
            'filename' => $this->filename
        ];
    }
}
