<?php

namespace App\Repositories\Marketing\Facebook;

use App\Repositories\Repository;

interface ImageRepositoryInterface extends Repository {
    /**
     * Delete All Images By Listing ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteAll(int $id): bool;
}