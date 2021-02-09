<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Exceptions\Common\BusyJobException;
use App\Models\Bulk\Parts\BulkReport;
use App\Models\Bulk\Parts\BulkReportPayload;
use App\Services\Common\MonitoredJobServiceInterface;

interface BulkReportJobServiceInterface extends MonitoredJobServiceInterface
{
    /**
     * @param int $dealerId
     * @param BulkReportPayload|array $payload
     * @param string|null $token
     * @return BulkReport
     * @throws BusyJobException when there is currently other job working
     */
    public function setup(int $dealerId, $payload, ?string $token = null);

    /**
     * @param BulkReport $job
     * @return mixed
     */
    public function dispatch($job): void;

    /**
     * @param BulkReport $job
     */
    public function dispatchNow($job): void;
}
