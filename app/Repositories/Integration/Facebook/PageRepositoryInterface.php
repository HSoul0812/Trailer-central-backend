<?php

namespace App\Repositories\Integration\Facebook;

use App\Repositories\Repository;

interface PageRepositoryInterface extends Repository {
    /**
     * Get Page by Facebook Page ID
     * 
     * @param array $params
     * @return Catalog
     */
    public function getByPageId($params);
}