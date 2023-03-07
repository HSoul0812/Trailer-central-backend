<?php

namespace App\Repositories\Website;

use App\Repositories\Repository;

interface EntityRepositoryInterface extends Repository {

    /**
     * Retrieves all pages for a given website
     */
    public function getAllPages($websiteId);

    /**
     * Updates config
     */
    public function updateConfig($websiteId, array $params);
}
