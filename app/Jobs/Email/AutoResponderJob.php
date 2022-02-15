<?php

namespace App\Jobs\Email;

use App\Jobs\Job;
use App\Mail\AutoResponderEmail;
use App\Models\CRM\Leads\Lead;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Class AutoResponder
 * @package App\Jobs\Email
 */
class AutoResponderJob extends Job
{
    /**
     * @var Lead
     */
    private $lead;

    /**
     * AutoResponder constructor.
     * @param Lead $lead
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;
    }

    public function handle()
    {
        Log::info('Starting AutoResponder', ['lead' => $this->lead->identifier]);

        $dealer = $this->lead->user;
        $log = Log::channel('inquiry');

        if (!$dealer->autoresponder_enable || empty($dealer->autoresponder_text)) {
            return false;
        }

        if (!filter_var($this->lead->email_address, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            Mail::to($this->lead->email_address)->send(
                new AutoResponderEmail([
                    'subject' => $dealer->autoresponder_text,
                ])
            );

            $log->info('AutoResponder successfully completed', ['lead' => $this->lead->identifier]);
            return true;
        } catch (\Exception $e) {
            $log->error('AutoResponder error', $e->getTrace());
            throw $e;
        }
    }
}
