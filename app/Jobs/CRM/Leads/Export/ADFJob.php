<?php

namespace App\Jobs\CRM\Leads\Export;

use App\Jobs\Job;
use App\Mail\CRM\Leads\Export\ADFEmail;
use App\Models\CRM\Leads\Lead;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Services\CRM\Leads\DTOs\ADFLead;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ADFJob
 * @package App\Jobs\CRM\Leads\Export
 */
class ADFJob extends Job
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var InquiryLead
     */
    private $inquiry;

    /**
     * @var Lead
     */
    private $lead;
    
    /**     
     * @var array
     */
    private $toEmails;
    
    /**
     * @var array
     */
    private $copiedEmails;
    
    /**
     *
     * @var array
     */
    private $hiddenCopiedEmails;
    
    /**
     * AutoResponder constructor.
     * @param InquiryLead $lead
     */
    public function __construct(InquiryLead $inquiry, Lead $lead, array $toEmails, array $copiedEmails, array $hiddenCopiedEmails = [])
    {
        $this->inquiry = $inquiry;
        $this->lead = $lead;
        $this->adf = $this->getAdfLead($inquiry);
        $this->toEmails = $toEmails;
        $this->copiedEmails = $copiedEmails;
        $this->hiddenCopiedEmails = $hiddenCopiedEmails;
    }

    public function handle()
    {
        Log::info('Mailing ADF Lead', ['lead' => $this->inquiry->leadId, 'interaction' => $this->inquiry->interactionId]);

        try {
            Mail::to($this->toEmails) 
                ->cc($this->copiedEmails)
                ->bcc($this->hiddenCopiedEmails)
                ->send(
                    new ADFEmail($this->inquiry)
                );

            // Set ADF Export Date
            if(empty($this->lead->adf_email_sent)) {
                $this->lead->adf_email_sent = $this->inquiry->getAdfSent();
                $this->lead->save();
            }
            
            Log::info('ADF Lead Mailed Successfully', ['lead' => $this->lead->identifier]);
            return true;
        } catch (\Exception $e) {
            Log::error('ADFLead Mail error', $e->getTrace());
            throw $e;
        }
    }
}
