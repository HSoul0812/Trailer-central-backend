<?php

declare(strict_types=1);

namespace App\Models\Integration\CVR;

use App\Models\Common\MonitoredJobResult;

/**
 * @property-read array $errors
 */
class CvrFileResult extends MonitoredJobResult
{
    /**
     * @var array
     */
    protected $errors;

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
            'errors' => $this->errors,
            'status' => $this->status,
            'exception' => $this->exception
        ]);
    }
}
