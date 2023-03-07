<?php

namespace App\Jobs\CRM\Leads\Export;

use App\Jobs\Job;
use App\Mail\CRM\Leads\Export\IDSEmail;
use App\Models\CRM\Leads\Lead;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use App\Mail\InquiryEmail;

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
        $log->info('Mailing IDS Lead', ['lead' => $this->lead->identifier]);
        
        $inquiryLead = $inquiryEmailService->createFromLead($this->lead);
        
        try {
            Mail::to($this->toEmails) 
                ->bcc($this->hiddenCopiedEmails)
                ->send(
                    new IDSEmail([
                        'lead' => $this->lead,
                    ])
                );
            
            Mail::to($this->copiedEmails)
                ->bcc($this->hiddenCopiedEmails)
                ->send(
                    new InquiryEmail(
                      $inquiryLead
                    )
                );           

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
