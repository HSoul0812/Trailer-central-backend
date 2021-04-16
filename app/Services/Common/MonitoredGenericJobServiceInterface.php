<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Contracts\Support\DTO;
use App\Exceptions\Common\BusyJobException;
use App\Models\Common\MonitoredJob;
use App\Models\Common\MonitoredJobPayload;
use InvalidArgumentException;

interface MonitoredGenericJobServiceInterface extends MonitoredJobServiceInterface
{
    /**
     * @param int $dealerId
     * @param array|MonitoredJobPayload|DTO $payload
     * @param string|null $token
     * @param string $className a monitored job class name (FQN)
     * @return MonitoredJob
     * @throws BusyJobException when there is currently other job working
     * @throws InvalidArgumentException when the provided $className is not a inherited class from
     */
    public function setup(int $dealerId, $payload, ?string $token = null, string $className = MonitoredJob::class);
}
