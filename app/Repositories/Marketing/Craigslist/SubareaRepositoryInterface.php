<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Subarea;
use App\Repositories\Repository;

interface SubareaRepositoryInterface extends Repository {
    /**
     * Create OR Update Subarea
     * 
     * @param array $params
     * @return Subarea
     */
    public function createOrUpdate(array $params): Subarea;
}