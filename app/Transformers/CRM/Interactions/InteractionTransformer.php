<?php

namespace App\Transformers\CRM\Interactions;

use League\Fractal\TransformerAbstract;
use App\Models\CRM\Interactions\Interaction;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Transformers\CRM\User\SalesPersonTransformer;
use Carbon\Carbon;

class InteractionTransformer extends TransformerAbstract
{
    /**
     * @var SalesPersonTransformer
     */
    private $salesPersonTransformer;

    protected $defaultIncludes = [
        'salesPerson',
        'emailHistory'
    ];

    protected $availableIncludes = [
        'lead'
    ];

    /**
     * SalesPersonTransformer constructor.
     * @param SalesPersonTransformer $salesPersonTransformer
     * @param EmailHistoryTransformer $emailHistoryTransformer
     */
    public function __construct(SalesPersonTransformer $salesPersonTransformer, EmailHistoryTransformer $emailHistoryTransformer) {
        $this->salesPersonTransformer = $salesPersonTransformer;
        $this->emailHistoryTransformer = $emailHistoryTransformer;
    }

    /**
     * Transform Interaction
     *
     * @param Interaction $interaction
     * @return array
     */
    public function transform(Interaction $interaction): array
    {
        return [
            'id' => $interaction->interaction_id,
            'user_id' => $interaction->user_id,
            'type' => $interaction->interaction_type,
            'time' => Carbon::parse($interaction->interaction_time)->format('F d, Y g:i A'),
            'notes' => $interaction->interaction_notes,
            'contact_name' => $interaction->lead->full_name,
            'username' => $interaction->real_username,
            'to_no' => $interaction->to_no,
            'interaction_time' => $interaction->interaction_time
        ];
    }

    public function includeLead(Interaction $interaction)
    {
        return $this->item($interaction->lead, new LeadTransformer());
    }

    public function includeSalesPerson(Interaction $interaction)
    {
        if ($interaction->leadStatus && $interaction->leadStatus->salesPerson) {
            return $this->item($interaction->leadStatus->salesPerson, $this->salesPersonTransformer);
        } else {
            return $this->null();
        }
    }

    public function includeEmailHistory(Interaction $interaction)
    {
        return $this->collection($interaction->emailHistory, $this->emailHistoryTransformer);
    }
}
