<?php

namespace App\Services\CRM\Leads;

use App\Models\CRM\Leads\Lead;

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
     * Merge Lead
     * 
     * @param Lead $lead
     * @param array $params
     * @return Interaction
     */
    public function merge(Lead $lead, array $params): Interaction;
}