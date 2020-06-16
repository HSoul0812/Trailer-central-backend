<?php

namespace App\Repositories\Parts;

use App\Repositories\Repository;

interface CostModifierRepositoryInterface extends Repository {
    
    /**
     * 
     * Returns a CostModifier instance based on dealer id
     *
     * @param int $dealerId
     */
    public function getByDealerId($dealerId); 
    
}
