<?php

namespace App\Services\CRM\Leads\Export;

use App\Models\CRM\Leads\Lead;
use App\Services\CRM\Leads\DTOs\InquiryLead;

/**
 * @author David A Conway Jr.
 */
interface ADFServiceInterface {
    
    /**
     * Takes a lead and export it to ADF in XML format
     * 
     * @param InquiryLead $inquiry lead to export to IDS
     * @param Lead $lead lead to export to IDS
     * @return bool
     */
    public function export(InquiryLead $inquiry, Lead $lead) : bool;
    
}
