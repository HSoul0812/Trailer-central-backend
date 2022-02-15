<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Exceptions\Common\BusyJobException;
use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Bulk\Parts\BulkDownloadPayload;
use App\Services\Common\MonitoredJobServiceInterface;
use App\Services\Common\RunnableJobServiceInterface;

interface BulkDownloadMonitoredJobServiceInterface extends MonitoredJobServiceInterface, RunnableJobServiceInterface
{
    /**
     * @param int $dealerId
     * @param BulkDownloadPayload|array $payload
     * @param string|null $token
     * @return BulkDownload
     * @throws BusyJobException when there is currently other job working
     */
    public function setup(int $dealerId, $payload, ?string $token = null);

    /**
     * @param BulkDownload $job
     * @return mixed
     */
    public function dispatch($job): void;

    /**
     * @param BulkDownload $job
     */
    public function dispatchNow($job): void;
}
