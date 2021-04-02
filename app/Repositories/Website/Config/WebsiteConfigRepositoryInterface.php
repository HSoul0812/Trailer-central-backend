<?php

namespace App\Repositories\Website\Config;

use App\Repositories\Repository;

interface WebsiteConfigRepositoryInterface extends Repository {
    /**
     * Get Value of Key For Website or Default
     * 
     * @param int $websiteId
     * @param string $key
     * @return array{key: value} or array{json_decode(values_mapping)}
     */
    public function getValueOrDefault(int $websiteId, string $key): array;
}
