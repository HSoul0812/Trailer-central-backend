<?php

namespace App\Services\CRM\Leads\Import;

use App\Models\CRM\Leads\LeadEmail;
use Illuminate\Support\Collection;

/**
 * @author David A Conway Jr.
 */
interface ADFServiceInterface {
    /**
     * Takes a lead and export it to the IDS system in XML format
     * 
     * @param App\Models\CRM\Leads\Lead $lead lead to export to IDS
     */
    public function import(Collection $leadEmails) : bool;
    
}
