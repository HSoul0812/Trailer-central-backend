<?php

declare(strict_types=1);

namespace App\Repositories\Website\Config\AvailableValues;

interface AvailableValuesRepositoryInterface
{
    /**
     * @return mixed
     */
    public function pull(int $websiteId);
}
