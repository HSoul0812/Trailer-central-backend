<?php

namespace App\Services\CRM\Interactions;

use App\Exceptions\CRM\Email\SendEmailFailedException;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Models\CRM\Email\Attachment;
use App\Mail\InteractionEmail;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

/**
 * Class InteractionEmailService
 * 
 * @package App\Services\CRM\Interactions
 */
class InteractionEmailService implements InteractionEmailServiceInterface
{
    use CustomerHelper, MailHelper;

    /**
     * Send Email With Params
     * 
     * @param int $dealerId
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @throws SendEmailFailedException
     * @return ParsedEmail
     */
    public function send(int $dealerId, SmtpConfig $smtpConfig, ParsedEmail $parsedEmail): ParsedEmail {
        // Get Unique Message ID
        if(empty($parsedEmail->getMessageId())) {
            $messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());
            $parsedEmail->setMessageId(sprintf('<%s>', $messageId));
        } else {
            $messageId = str_replace('<', '', str_replace('>', '', $parsedEmail->getMessageId()));
        }

        // Try/Send Email!
        try {
            // Fill Smtp Config
            $this->sendCustomEmail($smtpConfig, [
                'email' => $parsedEmail->getToEmail(),
                'name' => $parsedEmail->getToName()
            ], new InteractionEmail([
                'date' => Carbon::now()->setTimezone('UTC')->toDateTimeString(),
                'replyToEmail' => $smtpConfig->getUsername(),
                'replyToName' => $smtpConfig->getFromName(),
                'subject' => $parsedEmail->getSubject(),
                'body' => $parsedEmail->getBody(),
                'attach' => $parsedEmail->getAllAttachments(),
                'id' => $messageId
            ]));
        } catch(\Exception $ex) {
            throw new SendEmailFailedException($ex->getMessage());
        }

        // Store Attachments
        if($parsedEmail->hasAttachments()) {
            $parsedEmail->setAttachments($this->storeAttachments($dealerId, $parsedEmail));
        }

        // Returns Params With Attachments
        return $parsedEmail;
    }

    /**
     * Store Uploaded Attachments
     * 
     * @param int $dealerId
     * @param ParsedEmail $parsedEmail
     * @return Collection<Attachment>
     */
    public function storeAttachments(int $dealerId, ParsedEmail $parsedEmail): Collection {
        // Calculate Directory
        $messageDir = str_replace(">", "", str_replace("<", "", $parsedEmail->getMessageId()));

        // Valid Attachment Size?!
        if($parsedEmail->validateAttachmentsSize()) {
            // Loop Attachments
            $attachments = new Collection();
            foreach ($parsedEmail->getAllAttachments() as $file) {
                // Generate Path
                $filePath = 'crm/' . $dealerId . '/' . $messageDir . '/attachments/' . $file->getFileName();

                // Save File to S3
                Storage::disk('ses')->put($filePath, $file->getContents());

                // Set File Name/Path
                $file->setFilePath(Attachment::AWS_PREFIX . '/' . $filePath);
                $file->setFileName(time() . $file->getFileName());

                // Add File
                $attachments->push($file);
            }

            // Return Attachment Objects
            return $attachments;
        }

        // Return All Attachments
        return $parsedEmail->getAllAttachments();
    }
}
