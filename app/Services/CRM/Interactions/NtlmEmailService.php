<?php

namespace App\Services\CRM\Interactions;

use Illuminate\Support\Facades\Mail;
use App\Exceptions\CRM\Email\SendNtlmFailedException;
use App\Mail\InteractionEmail;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use Carbon\Carbon;

/**
 * Class NtlmEmailService
 * 
 * @package App\Services\CRM\Interactions
 */
class NtlmEmailService implements NtlmEmailServiceInterface
{
    use CustomerHelper, MailHelper;

    /**
     * Send Email With Params
     * 
     * @param int $dealerId
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @throws SendEmailFailedException
     */
    public function send(int $dealerId, SmtpConfig $smtpConfig, ParsedEmail $parsedEmail) {
        // Get Unique Message ID
        if(empty($parsedEmail->getMessageId())) {
            $messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());
            $parsedEmail->setMessageId(sprintf('<%s>', $messageId));
        } else {
            $messageId = str_replace('<', '', str_replace('>', '', $parsedEmail->getMessageId()));
        }

        // Try/Send Email!
        try {
            // Send Interaction Email
            Mail::to($this->getCleanTo([
                'email' => $parsedEmail->getToEmail(),
                'name' => $parsedEmail->getToName()
            ]))->send(new InteractionEmail([
                'date' => Carbon::now()->setTimezone('UTC')->toDateTimeString(),
                'replyToEmail' => $smtpConfig->getUsername(),
                'replyToName' => $smtpConfig->getFromName(),
                'subject' => $parsedEmail->getSubject(),
                'body' => $parsedEmail->getBody(),
                'attach' => $attachments,
                'id' => $messageId
            ]));
        } catch(\Exception $ex) {
            throw new SendNtlmFailedException($ex->getMessage());
        }

        // Store Attachments
        if(!empty($parsedEmail->getAttachments())) {
            $parsedEmail->setAttachments($this->storeAttachments($parsedEmail->getAttachments(), $dealerId, $messageId));
        }

        // Returns Params With Attachments
        return $parsedEmail;
    }
}
