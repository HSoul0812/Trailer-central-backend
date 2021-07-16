<?php

namespace App\Jobs\CRM\Interactions;

use App\Exceptions\CRM\Email\Builder\SendEmailBuilderJobFailedException;
use App\Jobs\Job;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class SendEmailBuilderJob
 * @package App\Jobs\CRM\Interactions
 */
class SendEmailBuilderJob extends Job
{ 
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var BuilderEmail
     */
    private $config;

    /**
     * SendEmailBuilderJob constructor.
     * @param BuilderEmail $config
     */
    public function __construct(BuilderEmail $config)
    {
        $this->config = $config;
    }

    /**
     * @param EmailBuilderServiceInterface $service
     * @throws SendEmailBuilderJobFailedException
     * @return boolean
     */
    public function handle(EmailBuilderServiceInterface $service) {
        // Initialize Logger
        $log = Log::channel('emailbuilder');
        $log->info('Mailing Email Builder Email', $this->config->getLogParams());

        try {
            // Send Email Via SMTP, Gmail, or NTLM
            $finalEmail = $service->sendEmail($this->config);

            // Mark Email as Sent
            $service->markSentMessageId($this->config, $finalEmail);
            $service->markEmailSent($finalEmail);
            $log->info('Email Builder Mailed Successfully', array_merge($this->config->getLogParams(), ['message_id' => $finalEmail->messageId]));
            return true;
        } catch (\Exception $e) {
            $log->error('Email Builder Mail error: ' . $e->getMessage(), $e->getTrace());
            throw new SendEmailBuilderJobFailedException();
        }
    }
}