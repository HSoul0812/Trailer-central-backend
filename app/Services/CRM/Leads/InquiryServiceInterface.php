<?php

namespace App\Services\CRM\Leads;

use App\Models\CRM\Leads\Lead;

interface InquiryServiceInterface {
    /**
     * Send Inquiry
     * 
     * @param array $params
     * @return Lead
     */
    public function send(array $params): Lead
}