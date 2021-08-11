<?php

namespace App\Repositories\Website;

use App\Repositories\Repository;

/**
 * Interface WebsiteDealerUrlRepositoryInterface
 * @package App\Repositories\Website
 */
interface WebsiteDealerUrlRepositoryInterface extends Repository
{
    /**
     * @param array $params
     * @return bool
     */
    public function exists(array $params): bool;
}
