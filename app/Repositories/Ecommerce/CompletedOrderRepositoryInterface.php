<?php

namespace App\Repositories\Ecommerce;

use App\Repositories\Repository;
use App\Repositories\TransactionalRepository;

interface CompletedOrderRepositoryInterface extends Repository, TransactionalRepository
{
    public function getAll($params);
}
