<?php

namespace App\Jobs\CRM\Leads\Export;

use App\Jobs\Job;
use App\Mail\CRM\Leads\Export\IDSEmail;
use App\Mail\InquiryEmail;
use App\Models\CRM\Leads\Lead;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use App\Services\CRM\Leads\DTOs\IDSLead;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class IDSJob
 * @package App\Jobs\CRM\Leads\Export
 */
class IDSJob extends Job
{ 
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * @param Lead $lead
     */
    public function __construct(Lead $lead, array $toEmails, array $copiedEmails, array $hiddenCopiedEmails = [])
    {
        $this->lead = $lead;
        $this->toEmails = $toEmails;
        $this->copiedEmails = $copiedEmails;
        $this->hiddenCopiedEmails = $hiddenCopiedEmails;
    }

    public function handle(InquiryEmailServiceInterface $inquiryEmailService)
    {
        if ($this->lead->ids_exported) {
            throw new \Exception('Already Exported');
        }

        // Initialize Log
        $log = Log::channel('leads-export');
        $log->info('Creating IDS Lead Details', ['lead' => $this->lead->identifier]);
        $ids = IDSLead::fromLead($this->lead);

        $log->info('Creating Inquiry Lead Details', ['lead' => $this->lead->identifier]);
        $inquiryLead = $inquiryEmailService->createFromLead($this->lead);

        try {
            $log->info('Attempt to Mail IDS Email', ['lead' => $this->lead->identifier, 'to' => $this->toEmails, 'bcc' => $this->hiddenCopiedEmails]);
            Mail::to($this->toEmails)
                ->bcc($this->hiddenCopiedEmails)
                ->send(
                    new IDSEmail($ids)
                );

            $log->info('Attempt to Mail Clone of Email Inquiry', ['lead' => $this->lead->identifier, 'to' => $this->copiedEmails, 'bcc' => $this->hiddenCopiedEmails]);
            Mail::to($this->copiedEmails)
                ->bcc($this->hiddenCopiedEmails)
                ->send(
                    new InquiryEmail($inquiryLead)
                );

            $log->info('Mark Lead as IDS Exported', ['lead' => $this->lead->identifier]);

            $this->lead->ids_exported = 1;
            $this->lead->save();

            // IDS Lead Sent
            $log->info('IDS Lead Mailed Successfully', ['lead' => $this->lead->identifier]);
            return true;
        } catch (\Exception $e) {
            // Flag it as exported anyway             
            $this->lead->ids_exported = 1;
            $this->lead->save();

            // IDS Lead Error
            $log->error('IDSLead Mail error', $e->getTrace());
            throw $e;
        }
    }
}
