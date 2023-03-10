<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\ClCity;
use App\Repositories\Repository;

interface CityRepositoryInterface extends Repository {
    /**
     * Create OR Update ClCity
     * 
     * @param array $params
     * @return ClCity
     */
    public function createOrUpdate(array $params): ClCity;
}