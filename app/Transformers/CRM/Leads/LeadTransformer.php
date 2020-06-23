<?php

namespace App\Transformers\CRM\Leads;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Leads\Lead;

class LeadTransformer extends TransformerAbstract {
    
    public function transform(Lead $lead)
    {   
        
	 $transformedLead =  [
             'id' => $lead->identifier,
             'name' => $lead->full_name,
             'inventory' => $lead->inventory,
             'interactions' => $lead->interactions,
             'status' => ($lead->leadStatus) ? $lead->leadStatus->status : null,
             'next_contact_date' => ($lead->leadStatus) ? $lead->leadStatus->next_contact_date : null,
             'created_at' => $lead->date_submitted,
             'contact_type' => ($lead->leadStatus) ? $lead->leadStatus->contact_type : null
         ];
         
         if (!empty($lead->pretty_phone_number)) {
             $transformedLead['phone'] = $lead->pretty_phone_number;
         }
                           
         return $transformedLead;
    }
    
}
