<?php

namespace App\Transformers\CRM\Leads\Export;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Leads\Export\LeadEmail;

class LeadEmailTransformer extends TransformerAbstract {
    
    public function transform(LeadEmail $leadEmail)
    {   
        return [
            'dealer_id' => $leadEmail->dealer_id,
            'email' => $leadEmail->email,
            'export_format' => $leadEmail->export_format,
            'cc_email' => $leadEmail->cc_email,
            'dealer_location_id' => $leadEmail->dealer_location_id
        ];
    }
}
