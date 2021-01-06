<?php

namespace App\Repositories\Integration\Facebook;

use App\Repositories\Repository;

interface CatalogRepositoryInterface extends Repository {
    /**
     * Create Facebook Catalog Feed
     * 
     * @param array $params
     * @return Feed
     */
    public function createFeed($params);

    /**
     * Update Facebook Catalog Feed
     * 
     * @param array $params
     * @return Feed
     */
    public function updateFeed($params);

    /**
     * Create or Update Facebook Catalog Feed
     * 
     * @param array $params
     * @return Feed
     */
    public function createOrUpdateFeed($params);
}