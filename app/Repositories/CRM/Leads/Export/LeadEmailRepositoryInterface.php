<?php

namespace App\Repositories\CRM\Leads\Export;

use App\Repositories\Repository;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\Export\LeadEmail;

interface LeadEmailRepositoryInterface extends Repository {
    
    /**
     * Gets a lead email object by dealer location id
     * 
     * @param int $dealerLocationId chunk size ot use
     * @return App\Models\CRM\Leads\LeadEmail
     */
    public function getLeadEmailByLead(Lead $lead) : LeadEmail;
    
}
