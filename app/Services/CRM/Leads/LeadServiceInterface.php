<?php

namespace App\Services\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Interactions\Interaction;

interface LeadServiceInterface {
    /**
     * Create Lead
     *
     * @param array $rawParams
     * @return Lead
     */
    public function create(array $rawParams): Lead;

    /**
     * Update Lead
     *
     * @param array $rawParams
     * @return Lead
     */
    public function update(array $rawParams): Lead;

    /**
     * Adding Sent Inquiry into Lead Interaction 
     *
     * @param Lead $lead
     * @param array $params
     * @return Interaction
     */
    public function mergeInquiry(Lead $lead, array $params): Interaction;

    /**
     * @param array $params
     * @return Lead
     */
    public function assign(array $params): Lead;

    /**
     * @param int $leadId
     * @param int $mergeLeadId
     * @return bool
     */
    public function mergeLeadData(int $leadId, int $mergeLeadId): bool;

    /**
     * Merge Leads
     * 
     * @param int $LeadId primary lead ID
     * @param array $mergeLeadIds lead IDs to be merged
     * @return void
     */
    public function mergeLeads(int $leadId, array $mergeLeadIds);
}
