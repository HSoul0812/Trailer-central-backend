<?php

namespace App\Transformers\CRM\Leads;

use App\Models\CRM\Leads\LeadStatus;
use League\Fractal\TransformerAbstract;

/**
 * Class LeadStatusTransformer
 * @package App\Transformers\CRM\Leads
 */
class LeadStatusTransformer extends TransformerAbstract
{
    /**
     * @param LeadStatus $leadStatus
     * @return array
     */
    public function transform(LeadStatus $leadStatus): array
    {
        return [
            'sales_person_id' => $leadStatus->sales_person_id,
            'status' => $leadStatus->status,
            'contact_type' => $leadStatus->contact_type,
            'next_contact_date' => $leadStatus->next_contact_date,
        ];
    }
}
