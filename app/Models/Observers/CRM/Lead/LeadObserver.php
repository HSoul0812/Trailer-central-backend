<?php

namespace App\Models\Observers\CRM\Lead;

use App\Models\CRM\Leads\Lead;
use App\Services\CRM\Interactions\InteractionMessageServiceInterface;

class LeadObserver
{
    /**
     * @var InteractionMessageServiceInterface
     */
    private $interactionMessageService;

    /**
     * @param InteractionMessageServiceInterface $interactionMessageService
     */
    public function __construct(InteractionMessageServiceInterface $interactionMessageService)
    {
        $this->interactionMessageService = $interactionMessageService;
    }

    /**
     * @param Lead $lead
     * @return void
     */
    public function updated(Lead $lead)
    {
        if (!$lead->wasChanged('lead_type')) {
            return;
        }

        $params['search_params']['lead_id'] = $lead->identifier;

        $this->interactionMessageService->bulkSearchable($params);
    }
}
