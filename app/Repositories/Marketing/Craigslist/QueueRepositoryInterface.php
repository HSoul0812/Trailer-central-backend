<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Queue;
use App\Repositories\Repository;

interface QueueRepositoryInterface extends Repository {
    /**
     * Find Queue
     * 
     * @param array $params
     * @return null|Queue
     */
    public function find(array $params): ?Queue;

    /**
     * Create OR Update Queue
     * 
     * @param array $params
     * @return Queue
     */
    public function createOrUpdate(array $params): Queue;
}