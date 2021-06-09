<?php

namespace App\Repositories\CRM\Leads\Export;

use App\Repositories\Repository;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\Export\LeadEmail;
use Illuminate\Database\Eloquent\Collection;
use App\DTO\CRM\Leads\Export\LeadEmail as LeadEmailDTO;

interface LeadEmailRepositoryInterface extends Repository {
    
    /**
     * Gets a lead email object by dealer location id
     * 
     * @param int $dealerLocationId chunk size ot use
     * @return App\Models\CRM\Leads\LeadEmail
     */
    public function getLeadEmailByLead(Lead $lead) : LeadEmail;
    
    /**
     * 
     * @param Collection leads<LeadEmailDTO>
     * @return bool
     */
    public function updateBulk(Collection $leads) : Collection;
    
    /**
     * 
     * @param int $dealerId
     * @param int $locationId
     * @return LeadEmail|null
     */
    public function getByDealerIdAndLocation(int $dealerId, int $locationId) : ?LeadEmail;
}
