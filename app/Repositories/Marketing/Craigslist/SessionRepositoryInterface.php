<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Session;
use App\Repositories\Repository;

interface SessionRepositoryInterface extends Repository {
    /**
     * Get Session
     * 
     * @param array $params
     * @return null|Session
     */
    public function find(array $params): ?Session;

    /**
     * Create OR Update Session
     * 
     * @param array $params
     * @return Session
     */
    public function createOrUpdate(array $params): Session;
}