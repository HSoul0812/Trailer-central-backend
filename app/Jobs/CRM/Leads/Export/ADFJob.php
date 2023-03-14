<?php

namespace App\Jobs\CRM\Leads\Export;

use App\Exceptions\CRM\Leads\Export\InvalidToEmailAddressException;
use App\Jobs\Job;
use App\Mail\CRM\Leads\Export\ADFEmail;
use App\Models\CRM\Leads\Lead;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Traits\ParsesEmails;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

/**
 * Class ADFJob
 * @package App\Jobs\CRM\Leads\Export
 */
class ADFJob extends Job
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use ParsesEmails;

    /**
     * @var ADFLead
     */
    private $adf;

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
     * ADF Export constructor.
     * @param ADFLead $adf
     * @param Lead $lead
     * @param array $toEmails
     * @param array $copiedEmails
     * @param array $hiddenCopiedEmails
     */
    public function __construct(ADFLead $adf, Lead $lead, array $toEmails, array $copiedEmails, array $hiddenCopiedEmails = [])
    {
        $this->adf = $adf;
        $this->lead = $lead;
        $this->toEmails = $this->parseEmails($toEmails);
        $this->copiedEmails = $this->parseEmails($copiedEmails);
        $this->hiddenCopiedEmails = $this->parseEmails($hiddenCopiedEmails);
    }

    /**
     * Handle ADF Job
     *
     * @return boolean
     * @throws \Exception
     */
    public function handle(): bool
    {
        // Initialize Log
        $log = Log::channel('leads-export');
        $log->info('Mailing ADF Lead', ['lead' => $this->adf->leadId]);

        try {
            if(empty($this->toEmails)) {
                throw new InvalidToEmailAddressException();
            }

            Mail::to($this->toEmails)
                ->cc($this->copiedEmails)
                ->bcc($this->hiddenCopiedEmails)
                ->send(
                    new ADFEmail($this->adf)
                );

            // Set ADF Export Date
            if (empty($this->lead->adf_email_sent)) {
                $this->lead->adf_email_sent = Carbon::now()->setTimezone('UTC')->toDateTimeString();
                $this->lead->save();
            }

            // ADF Lead Sent
            $log->info('ADF Lead Mailed Successfully', ['lead' => $this->adf->leadId]);
            return true;
        } catch (\Exception $e) {
            // ADF Lead Mail Error
            $log->error('ADFLead Mail error', $e->getTrace());
            throw $e;
        }
    }
}
