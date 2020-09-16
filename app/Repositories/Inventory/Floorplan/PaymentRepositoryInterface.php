<?php

namespace App\Repositories\Inventory\Floorplan;

use \App\Repositories\Repository;

/**
 *
 *
 * @author Marcel
 */
interface PaymentRepositoryInterface extends Repository {
    
    /**
     * Creates multiple floorplan payments
     * 
     * @param array $payments
     */
    public function createBulk($payments);
    
}
