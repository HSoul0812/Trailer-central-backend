<?php

namespace App\Repositories\CRM\Interactions;

use App\Repositories\Repository;

interface InteractionsRepositoryInterface extends Repository {
    /**
     * Save Email From Send Email
     * 
     * @param type $leadId
     * @param type $userId
     * @param type $params
     * @return type
     */
    public function saveEmail($leadId, $userId, $params);

    /**
     * Send Email to Lead
     * 
     * @param int $leadId
     * @param array $params
     * @return Interaction || error
     */
    public function sendEmail($leadId, $params);

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
