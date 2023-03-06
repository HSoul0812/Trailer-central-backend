<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Balance;
use App\Repositories\Repository;

interface BalanceRepositoryInterface extends Repository {
    /**
     * Create OR Update Balance
     * 
     * @param array $params
     * @return Balance
     */
    public function createOrUpdate(array $params): Balance;
}