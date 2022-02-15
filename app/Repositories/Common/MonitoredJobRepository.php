<?php

declare(strict_types=1);

namespace App\Repositories\Common;

use App\Contracts\Support\DTO;
use App\Models\Common\MonitoredJob;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder;

class MonitoredJobRepository implements MonitoredJobRepositoryInterface
{
    /**
     * Gets a single record by provided params
     *
     * @param array $params
     * @return MonitoredJob|Builder|null
     */
    public function get(array $params)
    {
        return MonitoredJob::where(array_key_first($params), current($params))->first();
    }

    /**
     * Gets all records by provided params
     *
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getAll(array $params): LengthAwarePaginator
    {
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

        $query = MonitoredJob::select('*');

        if (isset($params['dealer_id'])) {
            $query->where('dealer_id', $params['dealer_id']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    /**
     * @param string $token
     * @return MonitoredJob
     */
    public function findByToken(string $token)
    {
        return MonitoredJob::where('token', $token)->get()->first();
    }

    /**
     * @param array $params
     * @return MonitoredJob|Collection
     */
    public function create(array $params)
    {
        return MonitoredJob::create($params);
    }

    /**
     * @param MonitoredJob|string $job Job object or Job token
     * @return bool true if it has updated more than one record
     * @throws ModelNotFoundException when the job does not exists
     */
    public function setCompleted($job): bool
    {
        if (!$job instanceof MonitoredJob) {
            $job = MonitoredJob::findOrFail($job);
        }

        $job->progress = 100;

        return $job->save();
    }

    /**
     * @param string|MonitoredJob $job Job object or Job token
     * @param array|DTO $payload data with useful information about failure
     * @return bool true if it has updated more than one record
     * @throws ModelNotFoundException when the job does not exists
     */
    public function setFailed($job, $payload = []): bool
    {
        if (!$job instanceof MonitoredJob) {
            $job = MonitoredJob::findOrFail($job);
        }

        $job->status = MonitoredJob::STATUS_FAILED;
        $job->result = is_array($payload) ? $payload : $payload->asArray();

        return $job->save();
    }

    /**
     * @param string $token
     * @param array $params
     * @return bool true if it has updated more than one record
     * @throws ModelNotFoundException when the job does not exists
     */
    public function update(string $token, array $params): bool
    {
        unset($params['token']); // to prevent a token override

        $job = MonitoredJob::findOrFail($token);
        $job->fill($params);

        return $job->save();
    }

    /**
     * Updates the progress of a job by token
     *
     * @param string $token
     * @param float $progress
     * @return bool true if it has updated more than one record
     * @throws ModelNotFoundException when the job does not exists
     */
    public function updateProgress(string $token, $progress): bool
    {
        if ($progress >= 100) {
            return $this->setCompleted($token);
        }

        $job = MonitoredJob::findOrFail($token);
        $job->status = MonitoredJob::STATUS_PROCESSING;
        $job->progress = $progress;

        return $job->save();
    }

    /**
     * Updates the result of a job by token
     *
     * @param string $token
     * @param DTO $result
     * @return bool true if it has updated more than one record
     * @throws ModelNotFoundException when the job does not exists
     */
    public function updateResult(string $token, DTO $result): bool
    {
        $job = MonitoredJob::findOrFail($token);
        $job->result = $result->asArray();

        return $job->save();
    }

    public function isBusyByDealer(int $dealerId): bool
    {
        return MonitoredJob::where('dealer_id', $dealerId)
            ->where('status', MonitoredJob::STATUS_PROCESSING)
            ->exists();
    }

    public function isBusyByJobName(string $jobName): bool
    {
        return MonitoredJob::where('name', $jobName)
            ->where('status', MonitoredJob::STATUS_PROCESSING)
            ->exists();
    }
}
