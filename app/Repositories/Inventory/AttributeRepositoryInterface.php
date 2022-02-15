<?php

namespace App\Repositories\Inventory;

use App\Repositories\Repository;

/**
 * Interface AttributeRepositoryInterface
 * @package App\Repositories\Inventory
 */
interface AttributeRepositoryInterface extends Repository
{
    public function getAllByEntityTypeId(int $entityTypeId);
}
