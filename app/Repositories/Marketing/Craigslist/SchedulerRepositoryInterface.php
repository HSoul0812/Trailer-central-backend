<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Queue;
use App\Repositories\Repository;
use Illuminate\Pagination\LengthAwarePaginator;

interface SchedulerRepositoryInterface extends Repository {
    /**
     * Get the records for the scheduler
     *
     * @param $params
     *
     * @throws InvalidDealerIdException
     *
     * @return DBCollection
     */
    public function scheduler($params): DBCollection;

    /**
     * Get Upcoming Scheduler Posts
     * 
     * @param array $params
     * @return LengthAwarePaginator<Queue>
     */
    public function getUpcoming(array $params): LengthAwarePaginator;

    /**
     * Get All Scheduled Posts Now Ready
     *
     * @param array $params
     * @return LengthAwarePaginator<Queue>
     */
    public function getReady(array $params): LengthAwarePaginator;

    /**
     * Get All Queued Updated Posts Now Ready
     *
     * @param array $params
     * @return LengthAwarePaginator<Queue>
     */
    public function getUpdates(array $params): LengthAwarePaginator;

    /**
     * Get Posts Past Due
     * 
     * @array $params
     * @return int
     */
    public function duePast(array $params): int;

    /**
     * Get Posts Due Today
     * 
     * @array $params
     * @return int
     */
    public function dueToday(array $params): int;
}