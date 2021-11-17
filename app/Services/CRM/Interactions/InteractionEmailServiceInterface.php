<?php

namespace App\Services\CRM\Interactions;

use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\User\DTOs\EmailSettings;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Support\Collection;

interface InteractionEmailServiceInterface {
    /**
     * Get Email Config Settings
     * 
     * @param int $dealerId
     * @param null|int $salesPersonId
     * @return EmailSettings
     */
    public function config(int $dealerId, ?int $salesPersonId = null): EmailSettings;

    /**
     * Send Email With Params
     * 
     * @param EmailSettings $emailConfig
     * @param null|SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @throws SendEmailFailedException
     * @return ParsedEmail
     */
    public function send(EmailSettings $emailConfig, ?SmtpConfig $smtpConfig, ParsedEmail $parsedEmail): ParsedEmail;

    /**
     * Store Uploaded Attachments
     * 
     * @param int $dealerId
     * @param ParsedEmail $parsedEmail
     * @return Collection<Attachment>
     */
    public function storeAttachments(int $dealerId, ParsedEmail $parsedEmail): Collection;
}