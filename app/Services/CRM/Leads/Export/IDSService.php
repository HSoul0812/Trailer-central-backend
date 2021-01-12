<?php

namespace App\Services\CRM\Leads\Export;

use App\Services\CRM\Leads\Export\IDSServiceInterface;
use App\Models\CRM\Leads\Lead;
use App\Jobs\CRM\Leads\Export\IDSJob;
use Illuminate\Support\Facades\Log;
use App\Models\CRM\Leads\Export\LeadEmail;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;

class IDSService implements IDSServiceInterface {
    
    /**     
     * @var App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface 
     */
    protected $leadEmailRepository;
    
    public function __construct(LeadEmailRepositoryInterface $leadEmailRepository) {
        $this->leadEmailRepository = $leadEmailRepository;
    }
    
    public function export(Lead $lead) : bool {
        $leadEmail = $this->leadEmailRepository->getLeadEmailByLead($lead);

        if ($leadEmail->export_format !== LeadEMail::EXPORT_FORMAT_IDS) {
            return false;
        }
        
        IDSJob::dispatch($lead, $leadEmail->to_emails, $leadEmail->copied_emails)->onQueue('ids-export');
        
        return true;
    }
}
