<?php

namespace App\Jobs\CRM\Interactions;

use App\Exceptions\CRM\Email\Builder\EmailBuilderJobFailedException;
use App\Jobs\Job;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class EmailBuilderJob
 * @package App\Jobs\CRM\Interactions
 */
class EmailBuilderJob extends Job
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var BuilderEmail
     */
    private $config;

    /**
     * @var array
     */
    private $leads;

    /**
     * SendEmailBuilderJob constructor.
     * @param BuilderEmail $config
     */
    public function __construct(BuilderEmail $config, array $leads)
    {
        $this->config = $config;
        $this->leads = $leads;
    }

    /**
     * @param EmailBuilderServiceInterface $service
     * @throws EmailBuilderJobFailedException
     * @return boolean
     */
    public function handle(EmailBuilderServiceInterface $service) {
        // Initialize Logger
        $log = Log::channel('emailbuilder');
        $log->info('Processing ' . count($this->leads) . ' Email Builder Emails', $this->config->getLogParams());

        try {
            // Send Email Via SMTP, Gmail, or NTLM
            $stats = $service->sendEmails($this->config, $this->leads);

            // Handle Logging
            $log->info('Queued ' . $stats->noSent . ' Email ' .
                        $this->config->type . '(s) for Dealer #' . $this->config->userId);
            $log->info('Skipped ' . $stats->noSkipped . ' Email ' .
                        $this->config->type . '(s) for Dealer #' . $this->config->userId);
            $log->info('Errors Occurring Trying to Queue ' . $stats->noErrors . ' Email ' .
                        $this->config->type . '(s) for Dealer #' . $this->config->userId);
            return true;
        } catch (\Exception $e) {
            $log->error('Processing Email Builder Mail error: ' . $e->getMessage());
            throw new EmailBuilderJobFailedException();
        }
    }
}
