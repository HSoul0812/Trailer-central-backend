<?php

namespace App\Services\CRM\Email;

use App\Services\CRM\Leads\DTOs\InquiryLead;

interface InquiryEmailServiceInterface {
    /**
     * Send Email for Lead
     * 
     * @param LeadInquiry $inquiry
     * @throws SendInquiryFailedException
     * @return bool
     */
    public function send(InquiryLead $inquiry): bool;

    /**
     * Fill Inquiry Lead Details From Request Params
     * 
     * @param array $params
     * @return InquiryLead
     */
    public function fill(array $params): InquiryLead;
}