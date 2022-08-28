<?php

declare(strict_types=1);

namespace App\Services\Website;

use Illuminate\Support\Collection;

interface ExtraWebsiteConfigServiceInterface
{
    public function getAllByWebsiteId(int $websiteId): Collection;

    /**
     * @param int $websiteId
     * @param array{include_showroom: boolean, showroom_dealers: array<string>, global_filter: string} $params
     * @throws \Exception when something goes wrong at saving time
     */
    public function updateByWebsiteId(int $websiteId, array $params): void;
}
