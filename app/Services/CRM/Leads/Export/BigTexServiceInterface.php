<?php

namespace App\Services\CRM\Leads\Export;

use App\Models\CRM\Leads\Lead;

interface BigTexServiceInterface
{
    /**
     * Exports a lead to Big Tex
     * 
     * @param Lead $lead
     * @return bool
     */
    public function export(Lead $lead): bool;
}
