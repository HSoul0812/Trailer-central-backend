<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Transaction;
use App\Repositories\Repository;

interface TransactionRepositoryInterface extends Repository {
    /**
     * Create OR Update Transaction
     * 
     * @param array $params
     * @return Transaction
     */
    public function createOrUpdate(array $params): Transaction;
}