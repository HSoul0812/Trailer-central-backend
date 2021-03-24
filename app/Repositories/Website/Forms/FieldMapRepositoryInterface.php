<?php

namespace App\Repositories\Website\Forms;

use App\Repositories\Repository;

interface FieldMapRepositoryInterface extends Repository {
    /**
     * Get Map of Field Data
     * 
     * @param $params
     * @return Collection
     */
    public function getMap($params);
}
