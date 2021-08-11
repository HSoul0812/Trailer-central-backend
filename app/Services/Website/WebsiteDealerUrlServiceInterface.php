<?php

namespace App\Services\Website;

/**
 * Interface WebsiteDealerUrlServiceInterface
 * @package App\Services\Website
 */
interface WebsiteDealerUrlServiceInterface
{
    /**
     * @return array
     */
    public function generate(): array;

    /**
     * @param int $locationId
     * @return bool
     */
    public function generateByLocationId(int $locationId): bool;
}
