<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Session;
use App\Repositories\Repository;

interface SessionRepositoryInterface extends Repository {
    /**
     * Get Session
     * 
     * @param array $params
     * @return Session
     */
    public function find($params);

    /**
     * Create OR Update Session
     * 
     * @param array $params
     * @return Session
     */
    public function createOrUpdate(array $params): Session;
}