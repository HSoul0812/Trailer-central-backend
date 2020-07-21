<?php

namespace App\Repositories\User;
use App\Repositories\Repository;

interface DealerLocationRepositoryInterface extends Repository {
    /**
     * Get All Dealer SMS Numbers
     * 
     * @param int $dealerId
     * @return type
     */
    public function findAllDealerSmsNumbers($dealerId);

    /**
     * Get Dealer Number for Location or Default
     * 
     * @param int $dealerId
     * @param int $locationId
     * @return type
     */
    public function findDealerNumber($dealerId, $locationId);
}
