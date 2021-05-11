<?php

namespace App\Services\CRM\Email;

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
}