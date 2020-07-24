<?php

namespace App\Repositories\Inventory;

use App\Repositories\Repository;

interface CategoryRepositoryInterface extends Repository
{

    public function getAll($params, bool $paginated = false);

}
