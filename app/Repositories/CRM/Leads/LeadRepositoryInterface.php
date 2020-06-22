<?php

namespace App\Repositories\CRM\Leads;

use App\Repositories\Repository;

interface LeadRepositoryInterface extends Repository {
    
    /**
     * Returns array in the following format:
     * 
     * [
     *    'open' => 123,
     *    'closed_won' => 123,
     *    'closed_lost' => 123,
     *    'hot' => 123 
     * ]
     * 
     * @param int $dealerId 
     * @return array
     */
    public function getLeadStatusCountByDealer($dealerId);
    
}
