<?php

namespace App\Services\CRM\Email;

interface EmailBuilderServiceInterface {
    /**
     * Send Lead Emails for Blast
     * 
     * @param int $id ID of Blast to Send Emails For
     * @param array<int> ID's of Leads to Send Emails For Blast
     * @throws SendBlastEmailsFailedException
     * @return bool
     */
    public function sendBlast(int $id, array $leads): array;

    /**
     * Send Lead Emails for Campaign
     * 
     * @param int $id ID of Campaign to Send Emails For
     * @param array<int> ID's of Leads to Send Emails For Blast
     * @throws SendCampaignEmailsFailedException
     * @return bool
     */
    //public function sendCampaign(int $id, array $leads): array;

    /**
     * Send Lead Emails for Template
     *
     * @param int $id ID of Template to Send Emails For
     * @param string $email single email address to send template to
     * @throws SendTemplateEmailFailedException
     * @return bool
     */
    //public function sendTemplate(int $id, string $email): array;
}