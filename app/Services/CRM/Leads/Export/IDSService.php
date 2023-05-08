<?php

namespace App\Services\CRM\Leads\Export;

use App\Services\CRM\Leads\Export\IDSServiceInterface;
use App\Models\CRM\Leads\Lead;
use App\Jobs\CRM\Leads\Export\IDSJob;
use App\Models\CRM\Leads\Export\LeadEmail;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;

class IDSService implements IDSServiceInterface
{    
    use DispatchesJobs;

    /**     
     * @var App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface 
     */
    protected $leadEmailRepository;

    /**
     * @param Log $log
     */
    protected $log;


    /**
     * Construct LeadEmailRepository
     */
    public function __construct(LeadEmailRepositoryInterface $leadEmailRepository) {
        $this->leadEmailRepository = $leadEmailRepository;

        // Initialize Log
        $this->log = Log::channel('leads-export');
    }
    
    public function export(Lead $lead) : bool {
        // Get Getting Lead Email Details
        try {
            $leadEmail = $this->leadEmailRepository->getLeadEmailByLead($lead);
        } catch (ModelNotFoundException $ex) {
            $lead->ids_exported = 1;
            $lead->save();
            $this->log->error('IDS Lead Export Failed: ' . $ex->getMessage());
            return false;
        }

        // No Lead Email?
        if (empty($leadEmail->id)) {
            $this->log->error('IDS Lead Export Failed: Export Not Enabled for ' .
                                ' Dealer #' . $lead->dealer_id);
            return false;
        }

        // Export Format is Not IDS?
        if ($leadEmail->export_format !== LeadEmail::EXPORT_FORMAT_IDS) {
            $this->log->error('IDS Lead Export Failed: IDS Export Not Enabled for ' .
                                ' Dealer #' . $lead->dealer_id . ' and ' .
                                ' Dealer Location #' . $lead->dealer_location_id);
            return false;
        }

        // Get Hidden Copied Emails
        $hiddenCopiedEmails = explode(',', config('ids.copied_emails'));

        // Dispatch IDS Export Job
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
        // Get Getting Lead Email Details
        try {
            $leadEmail = $this->leadEmailRepository->getLeadEmailByLead($lead);
        } catch (ModelNotFoundException $ex) {
            $lead->ids_exported = 1;
            $lead->save();
            $this->log->error('IDS Lead Export Failed: ' . $ex->getMessage());
            return false;
        }

        // No Lead Email?
        if (empty($leadEmail->id)) {
            $this->log->error('IDS Lead Export Failed: Export Not Enabled for ' .
                                ' Dealer #' . $lead->dealer_id);
            return false;
        }

        // Export Format is Not IDS?
        if ($leadEmail->export_format !== LeadEmail::EXPORT_FORMAT_IDS) {
            $this->log->error('IDS Lead Export Failed: IDS Export Not Enabled for ' .
                                ' Dealer #' . $lead->dealer_id . ' and ' .
                                ' Dealer Location #' . $lead->dealer_location_id);
            return false;
        }

        // Get Copied Emails
        $hiddenCopiedEmails = explode(',', config('ids.copied_emails'));

        // Dispatch IDS Export Job
        $job = new IDSJob($lead, $leadEmail->to_emails, $leadEmail->copied_emails, $hiddenCopiedEmails);
        $this->dispatch($job->onQueue('inquiry'));
        return true;
    }
}
