<?php

namespace App\Services\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadAssign;
use App\Models\User\NewDealerUser;
use Illuminate\Database\Eloquent\Collection;

interface HotPotatoServiceInterface extends AutoAssignServiceInterface {
    /**
     * Handle Hot Potato for Dealer
     * 
     * @param User $dealer
     * @return Collection<LeadAssign>
     */
    public function dealer(NewDealerUser $dealer): Collection;
    
    /**
     * Handle Hot Potato for Lead
     * 
     * @param Lead $lead
     * @param Collection<array{key: value}> $settings
     * @return null|LeadAssign
     */
    public function hotPotato(Lead $lead, Collection $settings): ?LeadAssign;
    
}
