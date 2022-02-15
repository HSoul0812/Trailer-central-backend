<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Contracts\Support\DTO;
use App\Models\Common\MonitoredJob;

interface MonitoredJobServiceInterface
{
    /**
     * @param int $dealerId
     * @param DTO|array $payload
     * @param string|null $token
     * @return MonitoredJob
     */
    public function setup(int $dealerId, $payload, ?string $token);

    /**
     * Dispatch a job in an async way
     *
     * @param MonitoredJob $job
     * @return mixed
     */
    public function dispatch($job): void;

    /**
     * Dispatch a job in a sync way
     *
     * @param MonitoredJob $job
     */
    public function dispatchNow($job): void;
}
