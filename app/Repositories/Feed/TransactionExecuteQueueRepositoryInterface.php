<?php

namespace App\Repositories\Feed;

use App\Repositories\Repository;

interface TransactionExecuteQueueRepositoryInterface extends Repository
{
    /**
     * Stores the bulk data in the DB and returns an array of VINs
     * successfully stored
     */
    public function createBulk(array $atwInventoryData): array;
}
