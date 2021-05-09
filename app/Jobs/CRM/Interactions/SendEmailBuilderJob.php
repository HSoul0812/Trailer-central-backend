<?php

namespace App\Jobs\CRM\Interactions;

use App\Exceptions\CRM\Email\Builder\SendEmailBuilderJobFailedException;
use App\Jobs\Job;
use App\Mail\CRM\Interactions\EmailBuilderEmail;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\CRM\Email\CampaignRepositoryInterface;
use App\Repositories\CRM\Email\BlastRepositoryInterface;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Services\CRM\Interactions\NtlmEmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Traits\MailHelper;
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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MailHelper;

    /**
     * @var BuilderEmail
     */
    private $config;

    /**
     * SendEmailBuilder constructor.
     * @param BuilderEmail $config
     */
    public function __construct(BuilderEmail $config)
    {
        $this->config = $config;
    }

    /**
     * @param GoogleServiceInterface $googleService
     * @param GmailServiceInterface $gmailService
     * @param TokenRepositoryInterface $tokenRepo
     * @param EmailHistoryRepositoryInterface $emailHistoryRepo
     * @param TemplateRepositoryInterface $templateRepo
     * @param CampaignRepositoryInterface $campaignRepo
     * @param BlastRepositoryInterface $blastRepo
     * @throws SendEmailBuilderFailedException
     * @return boolean
     */
    public function handle(
        GoogleServiceInterface $googleService,
        GmailServiceInterface $gmailService,
        TokenRepositoryInterface $tokenRepo,
        NtlmEmailServiceInterface $ntlmService,
        EmailHistoryRepositoryInterface $emailHistoryRepo,
        TemplateRepositoryInterface $templateRepo,
        CampaignRepositoryInterface $campaignRepo,
        BlastRepositoryInterface $blastRepo
    ) {
        // Initialize Logger
        $log = Log::channel('emailbuilder');
        $log->info('Mailing Email Builder Email', $this->config->getLogParams());

        try {
            // Log to Database
            $email = $this->saveToDb($emailHistoryRepo);

            // Send Email Via SMTP, Gmail, or NTLM
            $finalEmail = $this->sendEmail($email->email_id, $googleService, $gmailService, $tokenRepo, $ntlmService);

            // Mark as Sent
            $this->markSent($emailHistoryRepo, $campaignRepo, $blastRepo, $finalEmail);
            $log->info('Email Builder Mailed Successfully', $this->config->getLogParams());
            return true;
        } catch (\Exception $e) {
            // Flag it as sent anyway
            $this->markSent($emailHistoryRepo, $campaignRepo, $blastRepo);
            $log->error('Email Builder Mail error: ' . $e->getMessage(), $e->getTrace());
            throw new SendEmailBuilderJobFailedException($e);
        }
    }


    /**
     * Save Email Information to Database
     * 
     * @param EmailHistoryRepositoryInterface $emailHistoryRepo
     * @return EmailHistory
     */
    private function saveToDb(EmailHistoryRepositoryInterface $emailHistoryRepo): EmailHistory {
        // Create or Update
        if(!empty($this->config->leadId)) {
            $interaction = $this->interactions->create([
                'lead_id'           => $this->config->leadId,
                'user_id'           => $this->config->userId,
                'sales_person_id'   => $this->config->salesPersonId,
                'interaction_type'  => 'EMAIL',
                'interaction_notes' => 'E-Mail Sent: ' . $this->config->subject,
                'interaction_time'  => Carbon::now()->setTimezone('UTC')->toDateTimeString(),
                'from_email'        => $this->config->fromEmail,
                'sent_by'           => $this->config->fromEmail
            ]);
        }

        // Create Email History Entry
        return $emailHistoryRepo->create(
            $this->config->getEmailHistoryParams($interaction->interaction_id ?? 0)
        );
    }

    /**
     * Send Email Via SMTP|Gmail|NTLM
     * 
     * @param GmailServiceInterface $gmailService
     * @param NtlmEmailServiceInterface $ntlmService
     * @return ParsedEmail
     */
    private function sendEmail(
        int $emailId,
        GoogleServiceInterface $googleService,
        GmailServiceInterface $gmailService,
        TokenRepositoryInterface $tokenRepo,
        NtlmEmailServiceInterface $ntlmService
    ): ParsedEmail {
        // Get Parsed Email
        $parsedEmail = $this->config->getParsedEmail($emailId);

        // Get SMTP Config
        if(!empty($this->config->smtpConfig->isAuthTypeGmail())) {
            // Get Access Token
            $accessToken = $this->refreshAccessToken($this->config->smtpConfig->accessToken, $googleService, $tokenRepo);
            $this->config->smtpConfig->setAccessToken($accessToken);
            var_dump($parsedEmail);

            // Send Gmail Email
            $finalEmail = $gmailService->send($this->config->smtpConfig, $parsedEmail);
        }
        // Get NTLM Config
        elseif(!empty($this->config->smtpConfig->isAuthTypeGmail())) {
            // Send NTLM Email
            $finalEmail = $ntlmService->send($this->config->smtpConfig, $parsedEmail);
        }
        // Get SMTP Config
        else {
            $this->setSmtpConfig($this->config->smtpConfig);

            // Send Email
            Mail::to($this->getCleanTo($this->config->getToEmail()))
                ->send(new EmailBuilderEmail($parsedEmail));
            $finalEmail = $parsedEmail;
        }

        // Return Final Email
        return $finalEmail;
    }

    /**
     * Refresh Gmail Access Token
     * 
     * @param AccessToken $accessToken
     * @param GoogleServiceInterface $googleService
     * @param TokenRepositoryInterface $tokenRepo
     * @return AccessToken
     */
    private function refreshAccessToken(
        AccessToken $accessToken,
        GoogleServiceInterface $googleService,
        TokenRepositoryInterface $tokenRepo
    ): AccessToken {
        // Refresh Token
        $validate = $googleService->validate($accessToken);
        if(!empty($validate['new_token'])) {
            $accessToken = $tokenRepo->refresh($accessToken->id, $validate['new_token']);
        }

        // Return New Token
        return $accessToken;
    }

    /**
     * Mark Email as Sent
     * 
     * @param EmailHistoryRepositoryInterface $emailHistoryRepo
     * @param CampaignRepositoryInterface $campaignRepo
     * @param BlastRepositoryInterface $blastRepo
     * @param null|ParsedEmail $finalEmail
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    private function markSent(
        EmailHistoryRepositoryInterface $emailHistoryRepo,
        CampaignRepositoryInterface $campaignRepo,
        BlastRepositoryInterface $blastRepo,
        ?ParsedEmail $finalEmail = null
    ): bool {
        // Set Date Sent
        if($finalEmail !== null) {
            $emailHistoryRepo->update([
                'id' => $finalEmail->emailHistoryId,
                'message_id' => $finalEmail->messageId,
                'body' => $finalEmail->body,
                'date_sent' => 1
            ]);
        }

        // Handle Based on Type
        switch($this->config->type) {
            case "campaign":
                $sent = $campaignRepo->sent([
                    'drip_campaigns_id' => $this->config->id,
                    'lead_id' => $this->config->leadId,
                    'message_id' => $finalEmail !== null ? $finalEmail->messageId : ''
                ]);
            break;
            case "blast":
                $sent = $blastRepo->sent([
                    'email_blasts_id' => $this->config->id,
                    'lead_id' => $this->config->leadId,
                    'message_id' => $finalEmail !== null ? $finalEmail->messageId : ''
                ]);
            break;
        }

        // Return False if Nothing Saved
        return !empty($sent->lead_id);
    }
}