<?php

namespace App\Repositories\Ecommerce;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Repositories\Repository;
use App\Repositories\TransactionalRepository;

interface CompletedOrderRepositoryInterface extends Repository, TransactionalRepository
{
    public function getAll($params);

    public function getGrandTotals(int $dealerId): array;

    public function create($params): CompletedOrder;

    /**
     * @param array $params
     * @return CompletedOrder
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function get($params): ?CompletedOrder;
}
