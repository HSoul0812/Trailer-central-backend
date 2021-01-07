<?php

declare(strict_types=1);

namespace App\Repositories\Inventory;

use App\Repositories\Repository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface InventoryHistoryRepositoryInterface extends Repository
{
    public const DEFAULT_GET_PARAMS = [
        self::CONDITION_AND_WHERE => [
            ['active', '=', 1],
            ['is_archived', '<>', 1]
        ]
    ];

    /**
     * @param  array  $params
     * @param  bool  $paginated
     * @return Collection|LengthAwarePaginator
     */
    public function getAll($params, bool $paginated = false);
}
