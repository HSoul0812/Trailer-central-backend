<?php

namespace App\Repositories\CRM\Interactions;

use App\Repositories\Repository;

interface InteractionsRepositoryInterface extends Repository {
    
    /**
     * Retrieves the list of tasks by dealer id
     * 
     * @param integer $dealerId
     * @return Collection
     */
    public function getTasksByDealerId($dealerId);
    
    /**
     * Returns list of available sort fields
     * 
     * @return array
     */
    public function getTasksSortFields();
    
}
