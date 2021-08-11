<?php

namespace App\Transformers\CRM\Interactions;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Interactions\Interaction;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Transformers\CRM\User\SalesPersonTransformer;
use Carbon\Carbon;

class InteractionTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'lead',
        'salesPerson',
        'emailHistory'
    ];

    /**
     * Transform Interaction
     *
     * @param Interaction $interaction
     * @return type
     */
    public function transform(Interaction $interaction)
    {
        return [
            'id' => $interaction->interaction_id,
            'type' => $interaction->interaction_type,
            'time' => Carbon::parse($interaction->interaction_time)->format('F d, Y g:i A'),
            'notes' => $interaction->interaction_notes,
            'contact_name' => $interaction->lead->full_name,
            'username' => $interaction->real_username,
            'to_no' => $interaction->to_no
        ];
    }

    public function includeLead(Interaction $interaction)
    {
        return $this->item($interaction->lead, new LeadTransformer());
    }

    public function includeSalesPerson(Interaction $interaction)
    {
        if ($interaction->leadStatus && $interaction->leadStatus->salesPerson) {
            return $this->item($interaction->leadStatus->salesPerson, new SalesPersonTransformer);
        } else {
            return $this->null();
        }
    }

    public function includeEmailHistory(Interaction $interaction)
    {
        return $this->collection($interaction->emailHistory, new EmailHistoryTransformer());
    }
}
