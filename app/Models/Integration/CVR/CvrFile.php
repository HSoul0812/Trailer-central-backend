<?php

declare(strict_types=1);

namespace App\Models\Integration\CVR;

use App\Contracts\Support\DTO;
use App\Models\Common\MonitoredJob;
use App\Repositories\Integration\CVR\CvrFileRepositoryInterface;

/**
 * @property CvrFilePayload $payload
 * @property CvrFileResult $result
 */
class CvrFile extends MonitoredJob
{
    public const QUEUE_JOB_NAME = 'cvr-send-file';

    public const VALIDATION_ERROR = 'validation_error';

    public const EXCEPTION_ERROR = 'exception_error';

    public const REPOSITORY_INTERFACE_NAME = CvrFileRepositoryInterface::class;
    
    public const QUEUE_NAME = 'cvr-send-file';

    /**
     * Payload accessor
     *
     * @param string|null $value
     * @return CvrFilePayload
     */
    public function getPayloadAttribute(?string $value): CvrFilePayload
    {
        return CvrFilePayload::from(json_decode($value ?? '', true));
    }

    /**
     * Result accessor
     *
     * @param string|null $value
     * @return CvrFileResult
     */
    public function getResultAttribute(?string $value): DTO
    {
        return CvrFileResult::from(json_decode($value ?? '', true));
    }
}
