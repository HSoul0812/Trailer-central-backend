<?php

declare(strict_types=1);

namespace App\Repositories\Website\Config\AvailableValues;

class EnabledFiltersRepository implements AvailableValuesRepositoryInterface
{
    public function pull(int $websiteId): string
    {
        return 'hello world '.$websiteId;
    }
}
