<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Repositories\Repository;
use Illuminate\Support\Collection;

interface BillingRepositoryInterface extends Repository
{
    /**
     * Get all Transactions between range
     *
     * @param array $params
     *
     * @return Collection
     */
    public function calendar(array $params): Collection;
}
