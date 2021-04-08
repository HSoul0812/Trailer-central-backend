<?php

namespace App\Repositories\CRM\Leads\Export;

use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Export\LeadEmail;
use App\Models\CRM\Leads\Lead;

class LeadEmailRepository implements LeadEmailRepositoryInterface 
{
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }
    
    public function getLeadEmailByLead(Lead $lead) : LeadEmail
    {
        $dealerLocationId = 0;

        if ($lead->dealerLocation) {
            $dealerLocationId = $lead->dealerLocation->dealer_location_id;
        }
        
        if ($lead->inventory) {
            $dealerLocationId = $lead->inventory->dealer_location_id;
        }
        
        
        return LeadEmail::where('dealer_location_id', $dealerLocationId)->where('dealer_id', $lead->website->dealer_id)->firstOrFail();
    }

}
