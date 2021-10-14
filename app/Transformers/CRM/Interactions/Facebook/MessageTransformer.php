<?php

namespace App\Transformers\CRM\Interactions;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Interactions\Interaction;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Transformers\CRM\User\SalesPersonTransformer;
use Carbon\Carbon;

class TaskTransformer extends TransformerAbstract 
{
    protected $leadTransformer;
    
    protected $salesPersonTransformer;
    
    public function __construct()
    {
        $this->leadTransformer = new LeadTransformer;
        $this->salesPersonTransformer = new SalesPersonTransformer;
    }
    public function transform(Interaction $interaction)
    {       
        $interactionTime = Carbon::parse($interaction->interaction_time);
	return [
           'task_date' => $interactionTime->format('Y-m-d'),
           'task_time' => $interactionTime->format('h:i A'),
           'type' => $interaction->interaction_type,
           'lead' => $this->leadTransformer->transform($interaction->lead),
           'notes' => $interaction->interaction_notes,
           'id' => $interaction->interaction_id,
           'contact_name' => $interaction->lead->getFullNameAttribute(),
           'sales_person' => $interaction->leadStatus->salesPerson ? $this->salesPersonTransformer->transform($interaction->leadStatus->salesPerson) : null
        ];
    }
}
