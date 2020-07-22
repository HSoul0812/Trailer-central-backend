<?php

namespace App\Transformers\CRM\Interactions;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Interactions\Interaction;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Transformers\CRM\User\SalesPersonTransformer;
use App\Models\CRM\Leads\Lead;
use Carbon\Carbon;

class InteractionTransformer extends TransformerAbstract 
{
    protected $interactionTransformer;
    protected $salesPersonTransformer;
    
    public function __construct()
    {
        $this->leadTransformer = new LeadTransformer;
        $this->salesPersonTransformer = new SalesPersonTransformer;
    }
    public function transform(Interaction $interaction) {
        // Check if Actually TextLog!
        if($interaction->interaction_type === 'TEXT_LOG') {
            $lead = $interaction->lead;
        }
        // Transform as Normal!
        else {
            $lead = Lead::findOrFail($interaction->tc_lead_id);
        }

        // Return Result!
        return [
            'id' => $interaction->interaction_id,
            'type' => $interaction->interaction_type,
            'time' => Carbon::parse($interaction->interaction_time),
            'notes' => $interaction->interaction_notes,
            'lead' => $this->leadTransformer->transform($lead),
            'contact_name' => $lead->full_name,
            'sales_person' => $lead->leadStatus->salesPerson ? $this->salesPersonTransformer->transform($lead->leadStatus->salesPerson) : null,
            'email_history' => $lead->emailHistory
        ];
    }
}
