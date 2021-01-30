<?php

declare(strict_types=1);

namespace App\Models\Bulk\Parts;

use App\Models\Common\MonitoredJobResult;

/**
 * @property-read array $validation_errors
 */
class BulkUploadResult extends MonitoredJobResult
{
    /**
     * @var array
     */
    protected $validation_errors;

    public function asArray(): array
    {
        return array_merge(parent::asArray(), ['validation_errors' => $this->validation_errors]);
    }
}
