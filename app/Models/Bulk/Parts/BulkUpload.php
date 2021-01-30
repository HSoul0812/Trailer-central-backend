<?php

namespace App\Models\Bulk\Parts;

use App\Contracts\Support\DTO;
use App\Models\Common\MonitoredJob;
use App\Repositories\Bulk\BulkUploadRepositoryInterface;

/**
 * @property BulkUploadPayload $payload
 * @property BulkUploadResult $result
 */
class BulkUpload extends MonitoredJob
{
    public const QUEUE_NAME = 'parts';

    public const QUEUE_JOB_NAME = 'parts-bulk-upload';

    public const VALIDATION_ERROR = 'validation_error';

    public const PROCESSING = parent::STATUS_PROCESSING; // for backward compatibility

    public const COMPLETE = parent::STATUS_COMPLETED; // for backward compatibility

    public const REPOSITORY_INTERFACE_NAME = BulkUploadRepositoryInterface::class;

    /**
     * Payload accessor
     *
     * @param string|null $value
     * @return BulkUploadPayload
     */
    public function getPayloadAttribute(?string $value)
    {
        return BulkUploadPayload::from(json_decode($value ?? '', true));
    }

    /**
     * Result accessor
     *
     * @param string|null $value
     * @return BulkUploadResult
     */
    public function getResultAttribute(?string $value): DTO
    {
        return BulkUploadResult::from(json_decode($value ?? '', true));
    }
}
