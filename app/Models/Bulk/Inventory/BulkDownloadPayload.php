<?php

declare(strict_types=1);

namespace App\Models\Bulk\Inventory;

use App\Models\Common\MonitoredJobPayload;

/**
 * @property-read string $filename filename of the finished file
 * @property-read string $output the type of the output e.g. pdf
 * @property-read array $filters list of used filters
 */
class BulkDownloadPayload extends MonitoredJobPayload
{
    /** @var string */
    protected $filename;

    /**
     * @var string
     */
    protected $output = 'pdf';

    /**
     * @var array
     */
    protected $filters = [];

    public function asArray(): array
    {
        return [
            'filename' => $this->filename,
            'output' => $this->output,
            'filters' => $this->filters ?? [],
        ];
    }
}
