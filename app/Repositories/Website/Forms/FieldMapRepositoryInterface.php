<?php

namespace App\Repositories\Website\Forms;

use App\Repositories\Repository;

interface FieldMapRepositoryInterface extends Repository {
    /**
     * Get All Sorted
     * 
     * @param $params
     * @param bool $withDefault
     * @return Collection
     */
    public function getAllSorted($params): Collection;
}
