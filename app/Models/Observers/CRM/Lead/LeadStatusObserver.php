<?php

namespace App\Models\Observers\CRM\Lead;

use App\Models\CRM\Leads\LeadStatus;
use App\Services\CRM\Interactions\InteractionMessageServiceInterface;

class LeadStatusObserver
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
     * @param LeadStatus $leadStatus
     * @return void
     */
    public function created(LeadStatus $leadStatus)
    {
        $params['search_params']['lead_id'] = $leadStatus->tc_lead_identifier;

        $this->interactionMessageService->bulkSearchable($params);
    }

    /**
     * @param LeadStatus $leadStatus
     * @return void
     */
    public function updated(LeadStatus $leadStatus)
    {
        if (!$leadStatus->wasChanged('sales_person_id')) {
            return;
        }

        $params['search_params']['lead_id'] = $leadStatus->tc_lead_identifier;

        $this->interactionMessageService->bulkSearchable($params);
    }

    /**
     * @param LeadStatus $leadStatus
     * @return void
     */
    public function deleted(LeadStatus $leadStatus)
    {
        $params['search_params']['lead_id'] = $leadStatus->tc_lead_identifier;

        $this->interactionMessageService->bulkSearchable($params);
    }

    /**
     * @param LeadStatus $leadStatus
     * @return void
     */
    public function updating(LeadStatus $leadStatus)
    {
        if ($leadStatus->closed_at === null) {

            if (in_array($leadStatus->status, [LeadStatus::STATUS_WON, LeadStatus::STATUS_WON_CLOSED, LeadStatus::STATUS_LOST])) {
        
                $leadStatus->closed_at = date('Y-m-d H:i:s');

            } else {

                $leadStatus->closed_at = null;
            }
        }
    }
}
