<?php

namespace App\Services\CRM\Leads;

interface InquiryEmailServiceInterface {
    /**
     * Send Inquiry Email for Lead
     * 
     * @param int $leadId
     * @param array $params
     * @throws SendInquiryFailedException
     */
    public function send(int $leadId, array $params);
}