<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Queue;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

interface SchedulerRepositoryInterface extends Repository {
    /**
     * Get Upcoming Scheduler Posts
     * 
     * @param array $params
     * @return Collection<Queue>
     */
    public function getUpcoming(array $params): Collection;
}