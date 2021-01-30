<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Contracts\Support\DTO;
use App\Exceptions\Common\BusyJobException;
use App\Models\Common\MonitoredJob;
use App\Models\Common\MonitoredJobPayload;

/**
 * Provide a generic way to set up and dispatch monitored jobs
 */
class MonitoredJobService extends AbstractMonitoredJobService implements MonitoredGenericJobServiceInterface
{
    /**
     * @param int $dealerId
     * @param array|MonitoredJobPayload|DTO $payload
     * @param string|null $token
     * @param string $queueName
     * @param string $concurrencyLevel
     * @param string $jobName
     * @return MonitoredJob
     * @throws BusyJobException when there is currently other job working
     */
    public function setup(
        int $dealerId,
        $payload,
        ?string $token = null,
        string $queueName = MonitoredJob::QUEUE_NAME,
        string $concurrencyLevel = MonitoredJob::LEVEL_WITHOUT_RESTRICTIONS,
        string $jobName = MonitoredJob::QUEUE_JOB_NAME
    ): MonitoredJob
    {
        if ($this->isAvailable($concurrencyLevel, $dealerId, $jobName)) {
            throw new BusyJobException("This job can't be set up due there is currently other job working");
        }

        return $this->repository->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => is_array($payload) ? $payload : $payload->asArray(),
            'queue' => $queueName,
            'concurrency_level' => $concurrencyLevel,
            'name' => $jobName
        ]);
    }
}
