<?php

namespace App\Repositories\Integration\Facebook;

use App\Repositories\Repository;

interface FeedRepositoryInterface extends Repository {
    /**
     * Create or Update Feed
     * 
     * @param array $params
     * @return Feed
     */
    public function createOrUpdate($params);
}