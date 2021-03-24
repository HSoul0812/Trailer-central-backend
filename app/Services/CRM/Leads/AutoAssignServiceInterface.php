<?php

namespace App\Services\CRM\Leads;

interface AutoAssignServiceInterface {
    
    /**
     * Auto Assigns a Sales Person for the given lead id
     * 
     * @param Lead $lead
     */
    public function autoAssign($lead);
    
}
