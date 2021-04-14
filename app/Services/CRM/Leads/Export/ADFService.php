<?php

namespace App\Services\CRM\Leads\Export;

use App\Services\CRM\Leads\Export\IDSServiceInterface;
use App\Models\CRM\Leads\Lead;
use App\Jobs\CRM\Leads\Export\ADFJob;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;

class IDSService implements IDSServiceInterface {
    
    /**     
     * @var App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface 
     */
    protected $leadEmailRepository;
    
    public function __construct(LeadEmailRepositoryInterface $leadEmailRepository) {
        $this->leadEmailRepository = $leadEmailRepository;
    }
    
    public function export(InquiryLead $inquiry, Lead $lead) : bool {
        $leadEmail = $this->leadEmailRepository->getLeadEmailByLead($lead);
        if ($leadEmail->export_format !== LeadEmail::EXPORT_FORMAT_ADF) {
            return false;
        }

        $hiddenCopiedEmails = explode(',', config('adf.exports.copied_emails'));
        
        ADFJob::dispatchNow($inquiry, $lead, $leadEmail->to_emails, $leadEmail->copied_emails, $hiddenCopiedEmails);
        
        return true;
    }
}
