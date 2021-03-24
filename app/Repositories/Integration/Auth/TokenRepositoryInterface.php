<?php

namespace App\Repositories\Integration\Auth;

use App\Repositories\Repository;

interface TokenRepositoryInterface extends Repository {
    /**
     * Find Exact Match Access Token
     * 
     * @param array $params
     * @return QueryBuilder
     */
    public function find($params);
}