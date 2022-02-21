<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Repositories\Repository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProfileRepositoryInterface extends Repository {

    /**
     * @return LengthAwarePaginator|null
     */
    public function getPaginator(): ?LengthAwarePaginator;
}
