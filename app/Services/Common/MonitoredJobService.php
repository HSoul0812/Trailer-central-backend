<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Contracts\Support\DTO;
use App\Exceptions\Common\BusyJobException;
use App\Models\Common\MonitoredJob;
use App\Models\Common\MonitoredJobPayload;
use InvalidArgumentException;

/**
 * Provide a generic way to set up and dispatch monitored jobs
 */
class MonitoredJobService extends AbstractMonitoredJobService implements MonitoredGenericJobServiceInterface
{
    /**
     * @param int $dealerId
     * @param array|MonitoredJobPayload|DTO $payload
     * @param string|null $token
     * @param string $className a monitored job class name (FQN)
     * @return MonitoredJob a inherited object from MonitoredJob or a MonitoredJob object
     * @throws BusyJobException when there is currently other job working
     * @throws InvalidArgumentException when the provided $className is not a inherited class from
     */
    public function setup(int $dealerId, $payload, ?string $token = null, string $className = MonitoredJob::class): MonitoredJob
    {
        /** @var MonitoredJob $monitoredJob */
        $monitoredJob = with($className);

        if (!$monitoredJob instanceof MonitoredJob) {
            throw new InvalidArgumentException(
                sprintf('%s must be a inherited class from %s', $className, MonitoredJob::class)
            );
        }

        $concurrencyLevel = $monitoredJob::LEVEL_DEFAULT;
        $queueName = $monitoredJob::QUEUE_NAME;
        $jobName = $monitoredJob::QUEUE_JOB_NAME;

        if ($this->isAvailable($concurrencyLevel, $dealerId, $jobName)) {
            throw new BusyJobException("This job can't be set up due there is currently other job working");
        }

        return $this->repositoryFrom($monitoredJob)->create([
            'dealer_id' => $dealerId,
            'token' => $token,
            'payload' => is_array($payload) ? $payload : $payload->asArray(),
            'queue' => $queueName,
            'concurrency_level' => $concurrencyLevel,
            'name' => $jobName
        ]);
    }
}
