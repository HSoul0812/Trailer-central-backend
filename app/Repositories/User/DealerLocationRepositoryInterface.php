<?php

namespace App\Repositories\User;
use App\Repositories\Repository;

interface DealerLocationRepositoryInterface extends Repository {
    /**
     * Find Dealer Location By Various Options
     * 
     * @param array $params
     * @return Collection<DealerLocation>
     */
    public function find($params);

    /**
     * Get First Dealer SMS Number
     * 
     * @param int $dealerId
     * @return type
     */
    public function findDealerSmsNumber($dealerId);

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
