<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Queue;
use App\Repositories\Repository;
use Illuminate\Pagination\LengthAwarePaginator;

interface SchedulerRepositoryInterface extends Repository {
    /**
     * Get Upcoming Scheduler Posts
     * 
     * @param array $params
     * @return LengthAwarePaginator<Queue>
     */
    public function getUpcoming(array $params): LengthAwarePaginator;
}