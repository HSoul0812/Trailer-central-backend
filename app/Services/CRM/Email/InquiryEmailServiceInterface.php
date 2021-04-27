<?php

namespace App\Services\CRM\Email;

use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Models\CRM\Leads\Lead;

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
    
    /**
     * Creates an Inquiry Lead from a lead
     * 
     * @param Lead $lead
     * @return InquiryLead
     */
    public function createFromLead(Lead $lead) : InquiryLead;
}