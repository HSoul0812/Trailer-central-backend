<?php

declare(strict_types=1);

namespace App\Repositories\Website\Config\AvailableValues;

use App\Models\Website\Config\WebsiteConfigDefault;

interface WebsiteConfigDefaultAvailableValuesRepositoryInterface
{
    /**
     * @param WebsiteConfigDefault $config
     * @param int $websiteId
     * @return mixed
     */
    public function getCustomAvailableValuesFor(WebsiteConfigDefault $config, int $websiteId);
}
