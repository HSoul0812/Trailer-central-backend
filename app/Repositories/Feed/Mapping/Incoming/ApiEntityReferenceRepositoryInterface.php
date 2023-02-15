<?php

declare(strict_types=1);

namespace App\Repositories\Feed\Mapping\Incoming;

use App\Repositories\GenericRepository;
use App\Repositories\Repository;

interface ApiEntityReferenceRepositoryInterface extends GenericRepository, Repository
{
    public function updateMultiples(array $conditions, array $newData): int;
}


