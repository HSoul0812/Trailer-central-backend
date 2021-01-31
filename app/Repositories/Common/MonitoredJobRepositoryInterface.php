<?php

declare(strict_types=1);

namespace App\Repositories\Common;

use App\Contracts\Support\DTO;
use App\Models\Common\MonitoredJob;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder;

/**
 * Describe the API for the repository of monitored jobs
 */
interface MonitoredJobRepositoryInterface
{
    /**
     * Gets a single record by provided params
     *
     * @param array $params
     * @return MonitoredJob|Builder|null
     */
    public function get(array $params);

    /**
     * Gets all records by provided params
     *
     * @param array $params
     * @returns mixed list of MonitoredJob
     */
    public function getAll(array $params);

    /**
     * Find a download by token
     *
     * @param string $token
     * @return MonitoredJob
     */
    public function findByToken(string $token);

    /**
     * Creates a single record by provided params
     *
     * @param array $params Array of values for the new row
     * @return MonitoredJob
     */
    public function create(array $params);

    /**
     * @param string|MonitoredJob $job Job object or Job token
     * @return bool true if it has updated more than one record
     */
    public function setCompleted($job): bool;

    /**
     * @param string|MonitoredJob $job Job object or Job token
     * @param array|DTO $payload data with useful information about failure
     * @return bool true if it has updated more than one record
     * @throws ModelNotFoundException when the job does not exists
     */
    public function setFailed($job, $payload): bool;

    /**
     * Updates a job by token
     *
     * @param string $token
     * @param array $params
     * @return bool true if it has updated more than one record
     * @throws ModelNotFoundException when the job does not exists
     */
    public function update(string $token, array $params): bool;

    /**
     * Updates the progress of a job by token
     *
     * @param string $token
     * @param float $progress
     * @return bool true if it has updated more than one record
     * @throws ModelNotFoundException when the job does not exists
     */
    public function updateProgress(string $token, $progress): bool;

    /**
     * Updates the result of a job by token
     *
     * @param string $token
     * @param DTO $result
     * @return bool true if it has updated more than one record
     * @throws ModelNotFoundException when the job does not exists
     */
    public function updateResult(string $token, DTO $result): bool;

    /**
     * Check if there is currently a job by dealer id
     *
     * @param int $dealerId
     * @return bool
     */
    public function isBusyByDealer(int $dealerId): bool;

    /**
     * Check if there is currently a job by job name
     *
     * @param string $jobName
     * @return bool
     */
    public function isBusyByJobName(string $jobName): bool;
}
