<?php

declare(strict_types=1);

namespace App\Repositories\Feed\Mapping\Incoming;

use App\Repositories\GenericRepository;

interface ApiEntityReferenceRepositoryInterface extends GenericRepository
{
    public function updateMultiples(array $conditions, array $newData): int;
}


