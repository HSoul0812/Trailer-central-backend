<?php

namespace App\Services\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadAssign;
use App\Models\User\NewDealerUser;
use Illuminate\Database\Eloquent\Collection;

interface AutoAssignServiceInterface {
    /**
     * Handle Auto Assign for Dealer
     * 
     * @param User $dealer
     * @return Collection<LeadAssign>
     */
    public function dealer(NewDealerUser $dealer): Collection;
    
    /**
     * Handle Auto Assign for Lead
     * 
     * @param Lead $lead
     * @return null|LeadAssign
     */
    public function autoAssign(Lead $lead): ?LeadAssign;
    
}
