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
     * @param array $params optional filters
     * @return array
     */
    public function getLeadStatusCountByDealer($dealerId, $params = []);
    
    /**
     * Returns customers based on leads
     * 
     * @param array $params optional filters
     * @return Collection
     */
    public function getCustomers($params = []);
    
    /**
     * Returns lead types
     * 
     * @return array
     */
    public function getTypes();
    
    /**
     * Returns lead statuses
     * 
     * @return array
     */
    public function getStatuses();
    
    /**
     * Returns list of available sort fields
     * 
     * @return array
     */
    public function getLeadsSortFields();
    
}
