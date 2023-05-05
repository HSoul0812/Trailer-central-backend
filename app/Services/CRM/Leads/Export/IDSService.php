<?php

namespace App\Services\CRM\Leads\Export;

use App\Services\CRM\Leads\Export\IDSServiceInterface;
use App\Models\CRM\Leads\Lead;
use App\Jobs\CRM\Leads\Export\IDSJob;
use Illuminate\Support\Facades\Log;
use App\Models\CRM\Leads\Export\LeadEmail;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\DispatchesJobs;

class IDSService implements IDSServiceInterface
{    
    use DispatchesJobs;

    /**     
     * @var App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface 
     */
    protected $leadEmailRepository;
    
    public function __construct(LeadEmailRepositoryInterface $leadEmailRepository) {
        $this->leadEmailRepository = $leadEmailRepository;
    }
    
    public function export(Lead $lead) : bool {
        try {
            $leadEmail = $this->leadEmailRepository->getLeadEmailByLead($lead);
        } catch (ModelNotFoundException $ex) {
            $lead->ids_exported = 1;
            $lead->save();
            return false;
        }
        
        
        if ($leadEmail->export_format !== LeadEMail::EXPORT_FORMAT_IDS) {
            return false;
        }
        
        $hiddenCopiedEmails = explode(',', config('ids.copied_emails'));
        
        IDSJob::dispatchNow($lead, $leadEmail->to_emails, $leadEmail->copied_emails, $hiddenCopiedEmails);
        
        return true;
    }

    /**
     * Takes a lead and export it to IDS format
     *
     * @param Lead $lead lead to export to IDS
     * @return bool
     */
    public function exportInquiry(Lead $lead) : bool {
        try {
            $leadEmail = $this->leadEmailRepository->getLeadEmailByLead($lead);
        } catch (ModelNotFoundException $ex) {
            $lead->ids_exported = 1;
            $lead->save();
            return false;
        }
        
        
        if ($leadEmail->export_format !== LeadEMail::EXPORT_FORMAT_IDS) {
            return false;
        }
        
        $hiddenCopiedEmails = explode(',', config('ids.copied_emails'));
        
        // Dispatch IDS Export Job
        $job = new IDSJob($lead, $leadEmail->to_emails, $leadEmail->copied_emails, $hiddenCopiedEmails);
        $this->dispatch($job->onQueue('inquiry'));
        return true;
    }
}
