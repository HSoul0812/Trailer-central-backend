<?php

namespace App\Services\CRM\Interactions;

use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\Integration\Common\DTOs\ParsedEmail;

interface NtlmEmailServiceInterface {
    /**
     * Send Email With Params
     * 
     * @param int $dealerId
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @throws SendEmailFailedException
     */
    public function send(int $dealerId, SmtpConfig $smtpConfig, ParsedEmail $parsedEmail): ParsedEmail;
}