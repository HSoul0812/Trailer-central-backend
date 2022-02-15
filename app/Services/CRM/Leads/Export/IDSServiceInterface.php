<?php

namespace App\Services\CRM\Leads\Export;

use App\Models\CRM\Leads\Lead;

/**
 *
 * @author Eczek
 */
interface IDSServiceInterface {
    
    /**
     * Takes a lead and export it to the IDS system in XML format
     * 
     * @param App\Models\CRM\Leads\Lead $lead lead to export to IDS
     */
    public function export(Lead $lead) : bool;
    
}
