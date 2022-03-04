<?php

namespace App\Repositories\CRM\Leads\Export;

use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Export\LeadEmail;
use App\Models\CRM\Leads\Lead;
use App\DTO\CRM\Leads\Export\LeadEmail as LeadEmailDTO;
use Illuminate\Database\Eloquent\Collection;

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
        $query = LeadEmail::where('id', '>', 0);
        
        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }
        
        return $query->get();
    }

    public function update($params) {
        $leadEmail = $this->getByDealerIdAndLocation($params['dealer_id'], $params['dealer_location_id']);
        
        if ($leadEmail) {
            $leadEmail->fill($params);
        } else {
            $leadEmail = new LeadEmail();
            $leadEmail->fill($params);
        }
        
        $leadEmail->save();
        return $leadEmail;
    }

    /**
     * Find a Lead Email If Exists
     * 
     * @param int $dealerId
     * @param null|int $dealerLocationId
     * @return null|LeadEmail
     */
    public function find(int $dealerId, ?int $dealerLocationId = 0): ?LeadEmail
    {
        // Get Lead Email for Location
        $leadEmail = LeadEmail::where('dealer_location_id', $dealerLocationId ?? 0)->where('dealer_id', $dealerId)->first();
        if(!empty($leadEmail->to_emails)) {
            return $leadEmail;
        }

        // Get Lead Email for JUST Dealer
        return LeadEmail::where('dealer_location_id', 0)->where('dealer_id', $dealerId)->first();
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

    /**
     * {@inheritDoc}
     */
    public function updateBulk(Collection $leads) : Collection
    {
        $collection =  new Collection;
        foreach($leads as $leadEmailDto) {
            $collection->add($this->update([
                'dealer_id' => $leadEmailDto->getDealerId(),
                'email' => $leadEmailDto->getEmail(),
                'export_format' => $leadEmailDto->getExportFormat(),
                'cc_email' => $leadEmailDto->getCcEmail(),
                'dealer_location_id' => $leadEmailDto->getDealerLocationId()
            ]));
        }
        return $collection;
    }
    
    public function getByDealerIdAndLocation(int $dealerId, int $locationId) : ?LeadEmail
    {
        return LeadEmail::where('dealer_id', $dealerId)->where('dealer_location_id', $locationId)->first();
    }
}
