<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Indexers\ElasticSearchQueryResult;

interface InventoryElasticSearchRepositoryInterface
{
    public function search(array $filters): ElasticSearchQueryResult;
}
