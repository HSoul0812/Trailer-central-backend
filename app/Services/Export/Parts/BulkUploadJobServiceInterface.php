<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Models\Bulk\Parts\BulkUpload;
use App\Models\Bulk\Parts\BulkUploadPayload;
use App\Services\Common\MonitoredJobServiceInterface;

interface BulkUploadJobServiceInterface extends MonitoredJobServiceInterface
{
    /**
     * @param int $dealerId
     * @param BulkUploadPayload|array $payload
     * @param string|null $token
     * @return BulkUpload
     */
    public function setup(int $dealerId, $payload, ?string $token = null): BulkUpload;

    /**
     * @param BulkUpload $job
     * @return mixed
     */
    public function dispatch($job): void;

    /**
     * @param BulkUpload $job
     */
    public function dispatchNow($job): void;
}
