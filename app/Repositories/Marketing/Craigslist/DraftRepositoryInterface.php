<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Draft;
use App\Repositories\Repository;

interface DraftRepositoryInterface extends Repository {
    /**
     * Create OR Update Draft
     * 
     * @param array $params
     * @return Draft
     */
    public function createOrUpdate(array $params): Draft;
}