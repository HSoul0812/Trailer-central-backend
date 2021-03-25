<?php

namespace App\Services\CRM\Interactions;

interface InteractionEmailServiceInterface {
    /**
     * Send Email With Params
     * 
     * @param int $dealerId
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @throws SendEmailFailedException
     */
    public function send(int $dealerId, SmtpConfig $smtpConfig, ParsedEmail $parsedEmail);

    /**
     * Get Attachments
     * 
     * @param type $files
     */
    public function getAttachments($files);

    /**
     * Store Uploaded Attachments
     * 
     * @param array $files
     * @param int $dealerId
     * @param string $messageId
     * @return array of saved attachments
     */
    public function storeAttachments($files, $dealerId, $messageId);
}