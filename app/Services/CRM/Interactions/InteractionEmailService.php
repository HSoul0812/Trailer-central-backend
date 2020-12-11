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
     * @param array $params
     * @throws SendEmailFailedException
     */
    public function send($dealerId, $params) {
        // Get Unique Message ID
        if(empty($params['message_id'])) {
            $messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());
        } else {
            $messageId = str_replace('<', '', str_replace('>', '', $params['message_id']));
        }
        $params['message_id'] = sprintf('<%s>', $messageId);

        // Get Attachments
        $attachments = array();
        if(isset($params['attachments'])) {
            $attachments = $this->getAttachments($params['attachments']);
        }

        // Try/Send Email!
        try {
            // Send Interaction Email
            Mail::to($this->getCleanTo([
                'email' => $params['to_email'],
                'name' => $params['to_name']
            ]))->send(new InteractionEmail([
                'date' => Carbon::now()->toDateTimeString(),
                'replyToEmail' => $params['from_email'] ?? "",
                'replyToName' => $params['from_name'],
                'subject' => $params['subject'],
                'body' => $params['body'],
                'files' => $params['files'],
                'attach' => $attachments,
                'id' => $messageId
            ]));
        } catch(\Exception $ex) {
            throw new SendEmailFailedException($ex->getMessage());
        }

        // Store Attachments
        if(isset($params['attachments'])) {
            $params['attachments'] = $this->storeAttachments($params['attachments'], $dealerId, $messageId);
        }

        // Returns Params With Attachments
        return $params;
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
