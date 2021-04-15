<?php

declare(strict_types=1);

namespace App\Models\Integration\CVR;

use App\Models\Common\MonitoredJobResult;

/**
 * @property-read array $validation_errors
 */
class CvrFileResult extends MonitoredJobResult
{
    /**
     * @var array
     */
    protected $validation_errors;

    /**
     * @var array
     */
    protected $exception;

    /**
     * @var string
     */
    protected $status;

    public function asArray(): array
    {
        return array_merge(parent::asArray(), [
            'validation_errors' => $this->validation_errors,
            'status' => $this->status,
            'exception' => $this->exception
        ]);
    }
}
