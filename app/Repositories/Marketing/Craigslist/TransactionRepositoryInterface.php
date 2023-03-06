<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Transaction;
use App\Repositories\Repository;

interface TransactionRepositoryInterface extends Repository {
    /**
     * Find Transaction
     * 
     * @param array $params
     * @return null|Transaction
     */
    public function find(array $params): ?Transaction;

    /**
     * Create OR Update Transaction
     * 
     * @param array $params
     * @return Transaction
     */
    public function createOrUpdate(array $params): Transaction;
}