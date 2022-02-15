<?php

namespace App\Repositories\CRM\Leads;

use App\Models\CRM\Leads\LeadStatus;
use App\Repositories\Repository;

interface StatusRepositoryInterface extends Repository {
    /**
     * Create or Update Lead Status
     * 
     * @param array $params
     * @return LeadStatus
     */
    public function createOrUpdate($params);
}
