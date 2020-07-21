<?php

namespace App\Transformers\CRM\Leads;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Leads\Lead;

class CampaignLeadTransformer extends TransformerAbstract {
    
    public function transform(Lead $lead)
    {   
        
	 $transformedLead =  [
             'id' => $lead->identifier,
             'name' => $lead->full_name,
             'status' => ($lead->leadStatus) ? $lead->leadStatus->status : Lead::STATUS_UNCONTACTED,
             'next_contact_date' => ($lead->leadStatus) ? $lead->leadStatus->next_contact_date : null,
             'created_at' => $lead->date_submitted,
             'contact_type' => ($lead->leadStatus) ? $lead->leadStatus->contact_type : null,
             'email' => $lead->email_address,
             'phone' => $lead->phone_number,
             'preferred_contact' => $lead->preferred_contact
         ];
         
         if (!empty($lead->pretty_phone_number)) {
             $transformedLead['phone'] = $lead->pretty_phone_number;
         }
                           
         return $transformedLead;
    }
}
