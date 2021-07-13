<?php

namespace App\Services\CRM\Email;

use App\Models\CRM\Interactions\EmailHistory;
use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Services\Integration\Common\DTOs\ParsedEmail;

interface EmailBuilderServiceInterface {
    /**
     * Send Lead Emails for Blast
     * 
     * @param int $id ID of Blast to Send Emails For
     * @param array<int> ID's of Leads to Send Emails For Blast
     * @throws FromEmailMissingSmtpConfigException
     * @throws SendBlastEmailsFailedException
     * @return array response
     */
    public function sendBlast(int $id, array $leads): array;

    /**
     * Send Lead Emails for Campaign
     * 
     * @param int $id ID of Campaign to Send Emails For
     * @param array<int> ID's of Leads to Send Emails For Campaign
     * @throws FromEmailMissingSmtpConfigException
     * @throws SendCampaignEmailsFailedException
     * @return array response
     */
    public function sendCampaign(int $id, array $leads): array;

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
     * Save Email Information to Database
     * 
     * @param BuilderEmail $config
     * @return EmailHistory
     */
    public function saveToDb(BuilderEmail $config): EmailHistory;

    /**
     * Send Email Via SMTP|Gmail|NTLM
     * 
     * @param BuilderEmail $config
     * @param int $emailId
     * @return ParsedEmail
     */
    public function sendEmail(BuilderEmail $config): ParsedEmail;

    /**
     * Mark Email as Sent
     * 
     * @param BuilderEmail $config
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markSent(BuilderEmail $config): bool;

    /**
     * Mark Email as Sent
     * 
     * @param BuilderEmail $config
     * @param ParsedEmail $finalEmail
     * @return boolean true if marked as sent (for campaign/blast) | false if nothing marked sent
     */
    public function markEmailSent(ParsedEmail $finalEmail): bool;
}