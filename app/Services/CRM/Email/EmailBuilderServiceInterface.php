<?php

namespace App\Services\CRM\Email;

use App\Models\CRM\Email\Blast;
use App\Models\CRM\Interactions\EmailHistory;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Services\CRM\Interactions\DTOs\BuilderStats;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Support\Collection;

interface EmailBuilderServiceInterface {
    /**
     * Send Lead Emails for Blast
     *
     * @param Blast $blast Model of Blast to Send Emails For
     * @throws FromEmailMissingSmtpConfigException
     * @throws SendBlastEmailsFailedException
     * @return array response
     */
    public function sendBlast(Blast $blast): array;

    /**
     * Send Lead Emails for Campaign
     * 
     * @param int $id ID of Campaign to Send Emails For
     * @param string Comma-Delimited String of Lead ID's to Send Emails For Blast
     * @throws FromEmailMissingSmtpConfigException
     * @throws SendCampaignEmailsFailedException
     * @return array response
     */
    public function sendCampaign(int $id, string $leads): array;

    /**
     * Send Email for Template
     * 
     * @param int $id ID of Template to Send Email For
     * @param string $subject Subject of Email to Send
     * @param string $toEmail Email Address to Send To
     * @param int $salesPersonId ID of Sales Person to Send From
     * @param string $fromEmail Email to Send From
     * @throws FromEmailMissingSmtpConfigException
     * @throws SendTemplateEmailFailedException
     * @return array response
     */
    public function sendTemplate(int $id, string $subject, string $toEmail, int $salesPersonId = 0, string $fromEmail = ''): array;

    /**
     * Send Emails for Builder Config
     * 
     * @param BuilderEmail $builder
     * @param Collection<int> $leads
     * @throws SendBuilderEmailsFailedException
     * @return BuilderStats
     */
    public function sendEmails(BuilderEmail $builder, Collection $leads): BuilderStats;

    
    /**
     * Save Email Information to Database
     * 
     * @param BuilderEmail $builder
     * @return EmailHistory
     */
    public function saveToDb(BuilderEmail $builder): EmailHistory;

    /**
     * Send Email Via SMTP|Gmail|NTLM
     * 
     * @param BuilderEmail $builder
     * @param int $emailId
     * @return ParsedEmail
     */
    public function sendEmail(BuilderEmail $builder): ParsedEmail;

    /**
     * Mark Email as Sent
     * 
     * @param BuilderEmail $builder
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markSent(BuilderEmail $builder): bool;

    /**
     * Mark Email as Sent
     * 
     * @param BuilderEmail $builder
     * @param ParsedEmail $finalEmail
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markEmailSent(ParsedEmail $finalEmail): bool;
}