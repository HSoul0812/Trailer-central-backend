<?php

declare(strict_types=1);

namespace App\Models\Bulk\Inventory;

use App\Models\Common\MonitoredJobPayload;
use App\Services\Export\FilesystemPdfExporter;

/**
 * @property-read string $filename filename of the finished file
 * @property-read string $output the type of the output e.g. pdf
 * @property-read string $orientation portrait or landscape
 * @property-read array $filters list of used filters
 */
class BulkDownloadPayload extends MonitoredJobPayload
{
    /** @var string */
    protected $filename;

    /** @var string */
    protected $output = 'pdf';

    /** @var string */
    protected $orientation = FilesystemPdfExporter::ORIENTATION_PORTRAIT;

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
            'orientation' => $this->orientation
        ];
    }
}
