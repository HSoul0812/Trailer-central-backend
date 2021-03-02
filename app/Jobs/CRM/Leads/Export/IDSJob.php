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

    public function handle()
    {
        if ($this->lead->ids_exported) {
            throw new \Exception('Already Exported');
        }
        
        Log::info('Mailing IDS Lead', ['lead' => $this->lead->identifier]);

        try {
            Mail::to($this->toEmails) 
                ->cc($this->copiedEmails)
                ->bcc($this->hiddenCopiedEmails)
                ->send(
                    new IDSEmail([
                        'lead' => $this->lead,
                    ])
                );

            $this->lead->ids_exported = 1;
            $this->lead->save();
            
            Log::info('IDS Lead Mailed Successfully', ['lead' => $this->lead->identifier]);
            return true;
        } catch (\Exception $e) {
            Log::error('IDSLead Mail error', $e->getTrace());
            throw $e;
        }
    }
}
