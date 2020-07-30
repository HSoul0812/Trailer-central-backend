<?php

namespace App\Services\CRM\Leads;

interface InquiryEmailServiceInterface {
    /**
     * Send Inquiry Email for Lead
     * 
     * @param int $leadId
     * @throws SendInquiryFailedException
     */
    public function send($leadId);
}