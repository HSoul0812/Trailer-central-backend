<?php

namespace App\Repositories\Integration\Facebook;

use App\Repositories\Repository;

interface CatalogRepositoryInterface extends Repository {
    /**
     * Get Catalog by Facebook User ID
     * 
     * @param array $params
     * @return Catalog
     */
    public function getByFBId($params);
}