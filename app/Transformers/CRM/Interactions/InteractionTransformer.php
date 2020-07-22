<?php

namespace App\Transformers\CRM\Interactions;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Leads\Lead;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Transformers\CRM\User\SalesPersonTransformer;
use Carbon\Carbon;

class InteractionTransformer extends TransformerAbstract 
{
    protected $interactionTransformer;
    protected $leadTransformer;
    protected $salesPersonTransformer;

    protected $defaultIncludes = [
        'lead',
        'salesPerson',
        'emailHistory'
    ];
    
    public function __construct()
    {
        $this->leadTransformer = new LeadTransformer;
        $this->salesPersonTransformer = new SalesPersonTransformer;
    }

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
            'time' => Carbon::parse($interaction->interaction_time),
            'notes' => $interaction->interaction_notes,
            'contact_name' => $interaction->lead->full_name
        ];
    }

    public function includeLead(Interaction $interaction)
    {
        return $this->leadTransformer->transform($interaction->lead);
    }

    public function includeSalesPerson(Interaction $interaction)
    {
        if(!empty($interaction->leadStatus->salesPerson)) {
            return $this->salesPersonTransformer->transform($interaction->leadStatus->salesPerson);
        }
        return null;
    }

    public function includeEmailHistory(Interaction $interaction)
    {
        if($interaction->interaction_type !== 'TEXT') {
            return $interaction->emailHistory;
        }
        return [];
    }

    /**
     * Transform Lead Without Circular Loading!
     * 
     * @param \App\Transformers\CRM\Interactions\Lead $lead
     * @return type
     */
    public function transformLead(Lead $lead) {
        $transformedLead =  [
            'id' => $lead->identifier,
            'name' => $lead->full_name,
            'inventory_interested_in' => $lead->inventory ? $this->transformInventory($lead->inventory) : [],
            'status' => ($lead->leadStatus) ? $lead->leadStatus->status : Lead::STATUS_UNCONTACTED,
            'next_contact_date' => ($lead->leadStatus) ? $lead->leadStatus->next_contact_date : null,
            'created_at' => $lead->date_submitted,
            'contact_type' => ($lead->leadStatus) ? $lead->leadStatus->contact_type : null,
            'email' => $lead->email_address,
            'preferred_contact' => $lead->preferred_contact
        ];

        if (!empty($lead->pretty_phone_number)) {
            $transformedLead['phone'] = $lead->pretty_phone_number;
        }

        return $transformedLead;
    }
}
