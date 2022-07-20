<?php

declare(strict_types=1);

namespace App\Services\Website;

use Illuminate\Support\Collection;

interface ExtraWebsiteConfigServiceInterface
{
    public function getAll(int $websiteId): Collection;

    public function createOrUpdate(array $params): Collection;
}
