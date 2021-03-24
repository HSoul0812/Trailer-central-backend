<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Repositories\Repository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface InventoryHistoryRepositoryInterface extends Repository
{
    /**
     * @param  array  $params
     * @param  bool  $paginated
     * @return Collection|LengthAwarePaginator
     */
    public function getAll($params, bool $paginated = false);
}
