<?php

declare(strict_types=1);

namespace App\Models\Bulk\Parts;

use App\Models\Common\MonitoredJobResult;

/**
 * @property-read string $url
 * @property-read string $filename location of the finished file
 */
class BulkReportResult extends MonitoredJobResult
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $filename;

    public function asArray(): array
    {
        return array_merge(parent::asArray(), ['url' => $this->url, 'filename' => $this->filename]);
    }
}
