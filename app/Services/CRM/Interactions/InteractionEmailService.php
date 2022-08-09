<?php

namespace App\Services\CRM\Interactions;

use App\Exceptions\CRM\Email\SendEmailFailedException;
use App\Mail\InteractionEmail;
use App\Models\CRM\Email\Attachment;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\CRM\User\DTOs\EmailSettings;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
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
     * @param EmailSettings $emailConfig
     * @param null|SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @throws SendEmailFailedException
     * @return ParsedEmail
     */
    public function send(EmailSettings $emailConfig, ?SmtpConfig $smtpConfig, ParsedEmail $parsedEmail): ParsedEmail {
        // Get Unique Message ID
        if(empty($parsedEmail->getMessageId())) {
            $messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());
            $parsedEmail->setMessageId(sprintf('<%s>', $messageId));
        } else {
            $messageId = str_replace('<', '', str_replace('>', '', $parsedEmail->getMessageId()));
        }

        // Try/Send Email!
        try {
            // Get From Email
            $fromEmail = ($smtpConfig !== null) ? $smtpConfig->getUsername() : config('mail.from.address');
            Log::info('Send from ' . $fromEmail . ' to: ' .
                        $parsedEmail->getToName() . ' <' . $parsedEmail->getToEmail() . '>');

            // Create Interaction Email
            $interactionEmail = new InteractionEmail([
                'date' => Carbon::now()->setTimezone('UTC')->toDateTimeString(),
                'subject' => $parsedEmail->getSubject(),
                'body' => $parsedEmail->getBody(),
                'attach' => $parsedEmail->getAllAttachments(),
                'id' => $messageId
            ]);

            // Send Email
            if($emailConfig->config !== 'default' && $smtpConfig !== null) {
                $this->sendCustomEmail($smtpConfig, [
                    'email' => $parsedEmail->getToEmail(),
                    'name' => $parsedEmail->getToName()
                ], $interactionEmail);
            } else {
                $this->sendDefaultEmail($emailConfig, [
                    'email' => $parsedEmail->getToEmail(),
                    'name' => $parsedEmail->getToName()
                ], $interactionEmail);
            }

            // Store Attachments
            if($parsedEmail->hasAttachments()) {
                $parsedEmail->setAttachments($this->storeAttachments($emailConfig->dealerId, $parsedEmail));
            }
        } catch(\Exception $ex) {
            throw new SendEmailFailedException($ex->getMessage());
        }

        // Returns Params With Attachments
        return $parsedEmail;
    }

    /**
     * Store Uploaded Attachments
     * 
     * @param int $dealerId
     * @param ParsedEmail $parsedEmail
     * @throws ExceededSingleAttachmentSizeException
     * @throws ExceededTotalAttachmentSizeException
     * @return Collection<Attachment>
     */
    public function storeAttachments(int $dealerId, ParsedEmail $parsedEmail): Collection {
        // Calculate Directory
        $messageDir = str_replace(">", "", str_replace("<", "", $parsedEmail->getMessageId()));

        // Valid Attachment Size?!
        $parsedEmail->validateAttachmentsSize();

        // Loop Attachments
        $attachments = new Collection();
        foreach ($parsedEmail->getAllAttachments() as $file) {
            // Generate Path
            $filePath = 'crm/' . $dealerId . '/' . $messageDir . '/attachments/' . $file->getFileName();

            // Save File to S3
            Storage::disk('ses')->put($filePath, $file->getContents());

            // Set File Name/Path
            $file->setFilePath(Attachment::AWS_PREFIX . '/' . urlencode($filePath));
            $file->setFileName(time() . $file->getFileName());

            // Add File
            $attachments->push($file);
        }

        // Return Attachment Objects
        return $attachments;
    }
}
