<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Draft;
use App\Repositories\Repository;

interface DraftRepositoryInterface extends Repository {
    /**
     * Find Draft
     * 
     * @param array $params
     * @return null|Draft
     */
    public function find(array $params): ?Draft;

    /**
     * Create OR Update Draft
     * 
     * @param array $params
     * @return Draft
     */
    public function createOrUpdate(array $params): Draft;
}