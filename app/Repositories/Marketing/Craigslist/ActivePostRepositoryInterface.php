<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\ActivePost;
use App\Repositories\Repository;

interface ActivePostRepositoryInterface extends Repository {
    /**
     * Create OR Update ActivePost
     * 
     * @param array $params
     * @return ActivePost
     */
    public function createOrUpdate(array $params): ActivePost;
}