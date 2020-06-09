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
             'status' => $lead->leadStatus->status,
             'next_contact_date' => $lead->leadStatus->next_contact_date,
             'created_at' => $lead->date_submitted
         ];
         
         if (!empty($lead->pretty_phone_number)) {
             $transformedLead['phone'] = $lead->pretty_phone_number;
         }
                           
         return $transformedLead;
    }
    
}
