<?php

namespace App\Services\CRM\Leads;

use App\Models\CRM\Leads\LeadStatus;

interface LeadStatusServiceInterface
{
    /**
     * Create Lead Status
     *
     * @param array $rawParams
     * @return LeadStatus
     */
    public function create(array $rawParams): LeadStatus;

    /**
     * Create Lead Status
     *
     * @param array $rawParams
     * @return LeadStatus
     */
    public function update(array $rawParams): LeadStatus;
}
