<?php

namespace App\Services\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadAssign;
use App\Models\User\User;
use League\Fractal\Resource\Collection;

interface AutoAssignServiceInterface {
    /**
     * Handle Auto Assign for Dealer
     * 
     * @param User $dealer
     * @return Collection<LeadAssign>
     */
    public function dealer(User $dealer): Collection;
    
    /**
     * Handle Auto Assign for Lead
     * 
     * @param Lead $lead
     * @return null|LeadAssign
     */
    public function autoAssign(Lead $lead): LeadAssign;
    
}
