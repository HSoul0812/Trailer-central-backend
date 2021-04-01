<?php

namespace App\Repositories\Website\Config;

use App\Repositories\Repository;

interface WebsiteConfigRepositoryInterface extends Repository {
    /**
     * Get Value of Key For Website or Default
     * 
     * @param int $websiteId
     * @param string $key
     * @return string
     */
    public function getValueOrDefault(int $websiteId, string $key): string;
}
