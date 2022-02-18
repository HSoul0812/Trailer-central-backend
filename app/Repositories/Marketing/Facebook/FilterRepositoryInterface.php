<?php

namespace App\Repositories\Marketing\Facebook;

use App\Repositories\Repository;

interface FilterRepositoryInterface extends Repository {
    /**
     * Delete All Filters By Marketplace ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteAll(int $id): bool;
}