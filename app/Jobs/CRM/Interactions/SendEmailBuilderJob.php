<?php

namespace App\Jobs\CRM\Interactions;

use App\Jobs\Job;
use App\Mail\Interactions\EmailBuilderEmail;
use App\Models\CRM\Interactions\EmailHistory;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Services\CRM\Interactions\DTOs\EmailBuilderConfig;
use App\Services\Integration\Google\GmailServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
     * @var EmailBuilderConfig
     */
    private $config;

    /**
     * @var EmailHistory
     */
    private $email;

    /**
     * SendEmailBuilder constructor.
     * @param EmailBuilderConfig $config
     */
    public function __construct(EmailBuilderConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param GmailServiceInterface $gmail
     * @param EmailHistoryRepositoryInterface $emailHistoryRepo
     * @param TemplateRepositoryInterface $templateRepo
     * @param CampaignRepositoryInterface $campaignRepo
     * @param BlastRepositoryInterface $blastRepo
     * @throws SendEmailBuilderFailedException
     * @return boolean
     */
    public function handle(
        GmailServiceInterface $gmailService,
        EmailHistoryRepositoryInterface $emailHistoryRepo,
        TemplateRepositoryInterface $templateRepo,
        CampaignRepositoryInterface $campaignRepo,
        BlastRepositoryInterface $blastRepo
    ) {
        // Initialize Logger
        $log = Log::channel('emailbuilder');
        $log->info('Mailing Email Builder Email', $this->config->getLogParams());

        try {
            // Send Email Via SMTP or Gmail
            $this->sendEmail($gmailService);

            // Log to Database
            $this->saveToDb($emailHistoryRepo, $templateRepo, $campaignRepo, $blastRepo);

            // Mark as Sent
            $this->markSent($campaignRepo, $blastRepo);

            $log->info('Email Builder Mailed Successfully', $this->config->getLogParams());
            return true;
        } catch (\Exception $e) {
            // Flag it as sent anyway
            $this->markSent($templateRepo, $campaignRepo, $blastRepo);
            $log->error('Email Builder Mail error', $e->getTrace());
            throw new SendEmailBuilderFailedException($e);
        }
    }


    /**
     * Send Email Via SMTP or Gmail
     * 
     * @param GmailServiceInterface $gmailService
     * @return void
     */
    private function sendEmail(GmailServiceInterace $gmailService): void {
        // Get SMTP Config
        if($this->config->isSmtp()) {
            // Set SMTP Config
            $this->setSmtp($this->config->getSmtpConfig());

            // Send Email
            Mail::to($this->config->getToEmail())->send(new EmailBuilderEmail($this->config->getEmailData()));
        } else {
            // Get Gmail Access Token
            $parsedEmail = $gmailService->send($this->config->getAccessToken(), $this->config->getParsedEmail());

            // Append Message ID
            $this->config->setMessageId($parsedEmail->messageId);
        }
    }

    /**
     * Save Email Information to Database
     * 
     * @param EmailHistoryRepositoryInterface $emailHistoryRepo
     * @param TemplateRepositoryInterface $templateRepo
     * @param CampaignRepositoryInterface $campaignRepo
     * @param BlastRepositoryInterface $blastRepo
     * @return EmailHistory
     */
    private function saveToDb(
        EmailHistoryRepositoryInterface $emailHistoryRepo,
        TemplateRepositoryInterface $templateRepo,
        CampaignRepositoryInterface $campaignRepo,
        BlastRepositoryInterface $blastRepo
    ): EmailHistory {
        // Create Email History Entry
        return $emailHistoryRepo->create([
            
        ]);
    }

    /**
     * Mark Email as Sent
     * 
     * @param CampaignRepositoryInterface $campaignRepo
     * @param BlastRepositoryInterface $blastRepo
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    private function markSent(
        CampaignRepositoryInterface $campaignRepo,
        BlastRepositoryInterface $blastRepo
    ): bool {
        // Handle Based on Type
        switch($this->config->type) {
            case "campaign":
                $sent = $campaignRepo->sent([
                    'drip_campaign_id' => $this->config->typeId,
                    'lead_id' => $this->config->leadId,
                    'message_id' => $this->email->message_id
                ]);
            break;
            case "blast":
                $sent = $blastRepo->sent([
                    'drip_campaign_id' => $this->config->typeId,
                    'lead_id' => $this->config->leadId,
                    'message_id' => $this->email->message_id
                ]);
            break;
        }

        // Return False if Nothing Saved
        return !empty($sent->lead_id);
    }
}