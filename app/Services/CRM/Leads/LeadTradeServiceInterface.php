<?php

namespace App\Services\CRM\Leads;

use App\Models\CRM\Leads\LeadTrade;

interface LeadTradeServiceInterface {

    /**
     * @param array $params
     * @return LeadTrade
     */
    public function create(array $params);

    /**
     * @param array $params
     * @return LeadTrade
     */
    public function update(array $params): LeadTrade;

    /**
     * @param array $params
     * @return bool
     */
    public function delete(array $params): bool;
} 