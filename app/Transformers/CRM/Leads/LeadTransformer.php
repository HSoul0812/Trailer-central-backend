<?php

namespace App\Transformers\CRM\Leads;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Leads\Lead;
use App\Transformers\CRM\Interactions\InteractionTransformer;
use App\Transformers\Inventory\InventoryTransformer;
use App\Transformers\User\DealerLocationTransformer;

class LeadTransformer extends TransformerAbstract {
    
    protected $defaultIncludes = [
        'preferredLocation',        
        'inventoryInterestedIn'
    ];
    
    protected $availableIncludes = [
        'leads',
        'interactions'
    ];
    
    protected $inventoryTransformer;
    
    public function __construct()
    {
        $this->inventoryTransformer = new InventoryTransformer();
    }

    /**
     * Transform Full Lead!
     * 
     * @param Lead $lead
     * @return type
     */
    public function transform(Lead $lead)
    {        
        $transformedLead =  [
            'id' => $lead->identifier,
            'website_id' => $lead->website_id,
            'dealer_id' => $lead->dealer_id,
            'name' => $lead->full_name,
            'lead_types' => $lead->lead_types,
            'email' => $lead->email_address,
            'phone' => $lead->phone_number,
            'preferred_contact' => $lead->preferred_contact,
            'address' => $lead->full_address,
            'comments' => $lead->comments,
            'note' => $lead->note,
            'referral' => $lead->referral,
            'title' => $lead->title,
            'status' => ($lead->leadStatus) ? $lead->leadStatus->status : Lead::STATUS_UNCONTACTED,
            'source' => ($lead->leadStatus) ? $lead->leadStatus->source : '',
            'next_contact_date' => ($lead->leadStatus) ? $lead->leadStatus->next_contact_date : null,
            'contact_type' => ($lead->leadStatus) ? $lead->leadStatus->contact_type : null,
            'created_at' => $lead->date_submitted
        ];

        if (!empty($lead->pretty_phone_number)) {
            $transformedLead['phone'] = $lead->pretty_phone_number;
        }

        return $transformedLead;
    }
    
    public function includePreferredLocation(Lead $lead)
    {
        if (empty($lead->preferred_dealer_location)) {
            return null;
        }
        
        return $this->item($lead->preferred_dealer_location, new DealerLocationTransformer());
    }
    
    public function includeInteractions(Lead $lead)
    {
        if (empty($lead->interactions)) {
            return [];
        }
        
        return $this->collection($lead->interactions, new InteractionTransformer());
    }
    
    public function includeInventoryInterestedIn(Lead $lead)
    {
        if (empty($lead->units)) {
            return [];
        }
        
        return $this->collection($lead->units, new InventoryTransformer());
    }
}