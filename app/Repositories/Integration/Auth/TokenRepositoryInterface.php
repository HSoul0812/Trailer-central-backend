<?php

namespace App\Repositories\Integration\Auth;

use App\Repositories\Repository;
use App\Models\Integration\Auth\AccessToken;
use Illuminate\Database\Eloquent\Collection;

interface TokenRepositoryInterface extends Repository {
    /**
     * Get Access Token Via Relation
     * 
     * @param array $params
     * @return null|AccessToken
     */
    public function getRelation(array $params): ?AccessToken;

    /**
     * Get Access Tokens Via Relation
     * 
     * @param string $type
     * @param int $id
     * @return Collection<AccessToken>
     */
    public function getRelations(string $type, int $id): Collection;

    /**
     * Find Exact Match Access Token
     * 
     * @param array $params
     * @return QueryBuilder
     */
    public function find($params);
}