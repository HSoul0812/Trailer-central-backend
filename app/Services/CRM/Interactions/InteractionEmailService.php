<?php

namespace App\Services\CRM\Interactions;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Exceptions\CRM\Email\SendEmailFailedException;
use App\Exceptions\CRM\Email\ExceededTotalAttachmentSizeException;
use App\Exceptions\CRM\Email\ExceededSingleAttachmentSizeException;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Models\CRM\Email\Attachment;
use App\Mail\InteractionEmail;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use Carbon\Carbon;

/**
 * Class InteractionEmailService
 * 
 * @package App\Services\CRM\Interactions
 */
class InteractionEmailService implements InteractionEmailServiceInterface
{
    use CustomerHelper, MailHelper;

    /**
     * @var array
     */
    private $imageTypes = [
        'gif',
        'png',
        'jpeg',
        'jpg'
    ];

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

        // Add Existing Attachments
        $attachments = array();
        if(!empty($parsedEmail->getExistingAttachments())) {
            $attachments = $this->cleanAttachments($parsedEmail->getExistingAttachments());
        }

        // Get Attachments
        if(!empty($parsedEmail->getAttachments())) {
            $attach = $this->getAttachments($parsedEmail->getAttachments());
            $attachments = array_merge($attachments, $attach);
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
            throw new SendEmailFailedException($ex->getMessage());
        }

        // Store Attachments
        if(!empty($parsedEmail->getAttachments())) {
            $parsedEmail->setAttachments($this->storeAttachments($parsedEmail->getAttachments(), $dealerId, $messageId));
        }

        // Returns Params With Attachments
        return $parsedEmail;
    }

    /**
     * Clean Existing Attachments
     * 
     * @param type $files
     */
    public function cleanAttachments($files) {
        // Clean Existing Attachments
        $attachments = array();
        if (!empty($files) && is_array($files)) {
            // Loop Attachment Files
            foreach ($files as $file) {
                // Get File Name
                $parts = explode("/", $file);
                $filename = end($parts);
                $ext = explode(".", $filename);
                $mime = 'image/jpeg';
                $size = 0;
                if(!empty($ext[1])) {
                    if(in_array($ext[1], $this->imageTypes)) {
                        $mime = 'image/' . $ext[1];
                    } else {
                        $mime = 'text/' . $ext[1];
                    }
                }

                // Get Mime Type
                $headers = get_headers($file);
                if(!empty($headers)) {
                    foreach($headers as $header) {
                        if(strpos($header, 'Content-Type') !== false) {
                            $mime = str_replace('Content-Type: ', '', $header);
                        }
                        elseif(strpos($header, 'Content-Length') !== false) {
                            $size = str_replace('Content-Length: ', '', $header);
                        }
                    }
                }

                // Add to Array
                $attachments[] = [
                    'path' => $file,
                    'as'   => $filename,
                    'mime' => $mime,
                    'size' => $size
                ];
            }
        }

        // Return Filled Attachments Array
        return $attachments;
    }

    /**
     * Get Attachments
     * 
     * @param type $files
     */
    public function getAttachments($files) {
        // Check Size of Attachments
        $this->checkAttachmentsSize($files);

        // Get Attachments
        $attachments = array();
        if (!empty($files) && is_array($files)) {
            // Loop Attachment Files
            foreach ($files as $file) {
                // Add to Array
                $attachments[] = [
                    'path' => $file->getPathname(),
                    'as'   => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        // Return Filled Attachments Array
        return $attachments;
    }

    /**
     * Store Uploaded Attachments
     * 
     * @param array $files
     * @param int $dealerId
     * @param string $messageId
     * @return array of saved attachments
     */
    public function storeAttachments($files, $dealerId, $messageId) {
        // Calculate Directory
        $messageDir = str_replace(">", "", str_replace("<", "", $messageId));

        // Loop Attachments
        $attachments = array();
        if (!empty($files) && is_array($files)) {
            // Valid Attachment Size?!
            if($this->checkAttachmentsSize($files)) {
                // Loop Attachments
                foreach ($files as $file) {
                    // Generate Path
                    $filePath = '/crm/' . $dealerId . '/' . $messageDir .
                        '/attachments/' . $file->getClientOriginalName();

                    // Save File to S3
                    Storage::disk('ses')->put($filePath, $file->get());

                    // Create Attachment
                    $attachments[] = [
                        'message_id' => '<' . $messageId . '>',
                        'filename' => Attachment::AWS_PREFIX . $filePath,
                        'original_filename' => time() . $file->getClientOriginalName()
                    ];
                }
            }
        }

        // Return Attachment Objects
        return $attachments;
    }

    /**
     * @param $files - mail attachment(-s)
     * @return bool | string
     */
    private function checkAttachmentsSize($files) {
        // Calculate Total Size
        $totalSize = 0;
        foreach ($files as $file) {
            if ($file->getSize() > Attachment::MAX_FILE_SIZE) {
                throw new ExceededSingleAttachmentSizeException();
            } else if ($totalSize > Attachment::MAX_UPLOAD_SIZE) {
                throw new ExceededTotalAttachmentSizeException();
            }
            $totalSize += $file->getSize();
        }

        // Return Total Size
        return $totalSize;
    }
}
