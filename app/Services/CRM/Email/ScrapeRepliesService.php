<?php

namespace App\Services\CRM\Email;

use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\Email\ImapServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class ScrapeRepliesService
 * 
 * @package App\Services\CRM\Email
 */
class ScrapeRepliesService implements ScrapeRepliesServiceInterface
{
    /**
     * @var App\Services\Integration\Google\GoogleServiceInterface
     */
    protected $google;

    /**
     * @var App\Services\Integration\Google\GmailServiceInterface
     */
    protected $gmail;

    /**
     * @var App\Services\CRM\Email\ImapServiceInterface
     */
    protected $imap;

    /**
     * @var App\Repositories\CRM\Interactions\InteractionsRepositoryInterface
     */
    protected $interactions;

    /**
     * @var App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface
     */
    protected $emails;

    /**
     * @var App\Repositories\Integration\Auth\TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * @var App\Repositories\CRM\Leads\LeadRepositoryInterface
     */
    protected $leads;

    /**
     * ScrapeRepliesService constructor.
     */
    public function __construct(GoogleServiceInterface $google,
                                GmailServiceInterface $gmail,
                                ImapServiceInterface $imap,
                                InteractionsRepositoryInterface $interactions,
                                EmailHistoryRepositoryInterface $emails,
                                TokenRepositoryInterface $tokens,
                                LeadRepositoryInterface $leads)
    {
        // Initialize Services
        $this->google = $google;
        $this->gmail = $gmail;
        $this->imap = $imap;

        // Initialize Repositories
        $this->interactions = $interactions;
        $this->emails = $emails;
        $this->tokens = $tokens;
        $this->leads = $leads;
    }


    /**
     * Import Single Folder
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @param Folder $folder
     * @return total number of imported emails
     */
    public function import($dealer, $salesperson, $folder) {
        // Missing Folder Name?
        if(empty($folder->name)) {
            //$this->updateFolder($folder, false, false);
            return 0;
        }

        // Get From Google?
        if(!empty($salesperson->googleToken)) {
            $total = $this->importGoogle($dealer->id, $salesperson, $folder);
        }
        // Get From IMAP Instead
        else {
            $total = $this->importImap($dealer->id, $salesperson, $folder);
        }

        // Return Inserted Replies
        return $total;
    }

    /**
     * Import Google
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param EmailFolder $folder
     * @return false || array of email results
     */
    private function importGoogle($dealerId, $salesperson, $folder) {
        // Refresh Token
        $accessToken = $salesperson->googleToken;
        $validate = $this->google->validate($accessToken);
        if(!empty($validate['new_token'])) {
            $accessToken = $this->tokens->refresh($accessToken->id, $validate['new_token']);
        }

        // Get Emails From Google
        $messages = $this->gmail->messages($accessToken, $folder->name);

        // Loop Messages
        $total = 0;
        $skipped = 0;
        foreach($messages as $overview) {
            // Get Parsed Message
            $parsed = $this->gmail->message($overview);

            // Check if Exists
            if(empty($parsed['headers']['Subject']) || empty($parsed['headers']['Message-ID']) ||
               $this->emails->findMessageId($salesperson->user_id, $parsed['headers']['Message-ID'])) {
                // Delete All Attachments
                Log::info('Already Processed Email Message ' . $parsed['headers']['Message-ID']);
                $this->deleteAttachments($parsed['attachments']);
                unset($overview);
                continue;
            }

            // Verify if Email Exists!
            $direction = 'Received';
            $to = !empty($parsed['headers']['To']) ? $parsed['headers']['To'] : '';
            $from = !empty($parsed['headers']['From']) ? $parsed['headers']['From'] : '';
            $reply = !empty($parsed['headers']['Reply-To']) ? $parsed['headers']['Reply-To'] : '';

            // Get Lead Email Exists?
            $emails = [];
            if($salesperson->smtp_email !== $to) {
                $emails[] = $to;
                $direction = 'Sent';
            }
            if($salesperson->smtp_email !== $from) {
                $emails[] = $from;
            }
            if($salesperson->smtp_email !== $reply) {
                $email[] = $reply;
            }
            $lead = $this->leads->getByEmails($dealerId, $emails);

            // Valid Lead
            if(!empty($lead->identifier)) {
                // Get To Name
                $date = strtotime($parsed['headers']['Date']);
                $subject = $parsed['headers']['Subject'];
                $toName = $parsed['headers']['To-Name'];
                $fromName = $parsed['headers']['From-Name'];

                // Insert Interaction / Email History
                $params = [
                    'dealer_id' => $dealerId,
                    'user_id' => $salesperson->user_id,
                    'lead_id' => $lead->identifier,
                    'message_id' => $parsed['headers']['Message-ID'],
                    'to_email' => $to,
                    'to_name' => !empty($toName) ? $toName : '',
                    'from_email' => $from,
                    'from_name' => !empty($fromName) ? $fromName : '',
                    'subject' => !empty($subject) ? $subject : '',
                    'body' => !empty($parsed['body']) ? $parsed['body'] : '',
                    'attachments' => $parsed['attachments'],
                    'date_sent' => date("Y-m-d H:i:s", $date),
                    'direction' => $direction
                ];
                $message = $this->insertReply($params);

                // Valid?
                if(!empty($message->message_id)) {
                    $this->messageIds[] = $message->message_id;
                    $total++;
                }
                unset($message);
                unset($params);
            }
            // Mark as Skipped
            else {
                $this->emails->createProcessed($salesperson->user_id, $parsed['headers']['Message-ID']);
                $skipped++;
                Log::info("Skipped Email Message " . $parsed['headers']['Message-ID']);
            }

            // Clear Memory/Space
            $this->deleteAttachments($parsed['attachments']);
            unset($overview);
        }

        // Process Skipped Message ID's
        if($skipped > 0) {
            Log::info("Processed " . $skipped . " emails that were skipped and not imported.");
        }

        // Return Result Messages That Match
        return $total;
    }

    /**
     * Import Via Imap
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param EmailFolder $folder
     * @return false || array of email results
     */
    private function importImap($dealerId, $salesperson, $folder) {
        // Get Emails From IMAP
        $messages = $this->imap->messages($salesperson, $folder);

        // Loop Messages
        $total = 0;
        $skipped = 0;
        foreach($messages as $mailId) {
            // Get Message Overview
            $overview = $this->imap->overview($mailId);

            // Check if Exists
            if(empty($overview['subject']) || empty($overview['message_id']) ||
               $this->emails->findMessageId($salesperson->user_id, $overview['message_id'])) {
                // Delete All Attachments
                Log::info('Already Processed Email Message ' . $overview['message_id']);
                unset($overview);
                continue;
            }

            // Verify if Email Exists!
            $direction = 'Received';
            $to = $overview['to'];
            $from = $overview['from'];

            // Get Lead Email Exists?
            $emails = [];
            if($salesperson->smtp_email !== $to) {
                $emails[] = $to;
                $direction = 'Sent';
            }
            if($salesperson->smtp_email !== $from) {
                $emails[] = $from;
            }
            $lead = $this->leads->getByEmails($dealerId, $emails);

            // Valid Lead
            if(!empty($lead->identifier)) {
                // Get Full IMAP Data
                $parsed = $this->imap->parsed($overview);

                // Insert Interaction / Email History
                $params = [
                    'dealer_id' => $dealerId,
                    'user_id' => $salesperson->user_id,
                    'lead_id' => $lead->identifier,
                    'message_id' => $parsed['message_id'],
                    'root_message_id' => $parsed['root_id'],
                    'to_email' => $parsed['to'],
                    'to_name' => $parsed['to_name'],
                    'from_email' => $parsed['from'],
                    'from_name' => $parsed['from_name'],
                    'subject' => $parsed['subject'],
                    'body' => $parsed['body'],
                    'use_html' => $parsed['use_html'],
                    'attachments' => $parsed['attachments'],
                    'date_sent' => $parsed['date'],
                    'direction' => $direction
                ];
                $message = $this->insertReply($params);

                // Valid?
                if(!empty($message->message_id)) {
                    $total++;
                    $this->messageIds[] = $message->message_id;
                    Log::info("Inserted Email Message " . $message->message_id);
                }
                unset($message);
                unset($params);

                // Clear Memory/Space
                $this->deleteAttachments($parsed['attachments']);
                $messageId = $parsed['message_id'];
                unset($parsed);
                Log::info('Cleared Email Message ' . $messageId);
            }
            // Mark as Skipped
            else {
                $this->emails->createProcessed($salesperson->user_id, $overview['message_id']);
                $skipped++;
                Log::info("Skipped Email Message " . $overview['message_id']);
            }
        }
        unset($messages);

        // Process Skipped Message ID's
        if($skipped > 0) {
            Log::info("Processed " . $skipped . " emails that were skipped and not imported.");
        }

        // Return Result Messages That Match
        return $total;
    }

    /**
     * Insert Reply Into DB
     * 
     * @param array $reply
     * @return array
     */
    private function insertReply($reply) {
        // Start Transaction
        $email = array();
        DB::transaction(function() use (&$email, $reply) {
            // Insert Interaction
            $interaction = $this->interactions->create([
                'lead_id' => $reply['lead_id'],
                'user_id' => $reply['user_id'],
                'interaction_type' => 'EMAIL',
                'interaction_notes' => 'E-Mail ' . $reply['direction'] . ': ' . $reply['subject'],
                'interaction_time' => $reply['date_sent']
            ]);

            // Insert Attachments
            $reply['attachments'] = $this->insertAttachments($reply['dealer_id'], $reply['message_id'], $reply['attachments']);

            // Insert Email History Entry
            unset($reply['direction']);
            $reply['interaction_id'] = $interaction->interaction_id;
            $email = $this->emails->create($reply);
            unset($reply);
        });

        // Return Final Email
        return $email;
    }

    /**
     * Insert Attachments
     * 
     * @param string $messageId
     * @param array $files
     * @return Collection of Attachment
     */
    private function insertAttachments($dealerId, $messageId, $files) {
        // No Attachments?
        if(empty($files)) {
            return collect([]);
        }

        // Loop Attachments
        $attachments = array();
        foreach($files as $file) {
            // Skip Entry
            if(empty($file->filePath)) {
                continue;
            }

            // Upload File to S3
            $messageDir = str_replace(">", "", str_replace("<", "", $messageId));
            $path_parts = pathinfo( $file->name );
            $filename = $path_parts['filename'];
            $ext = !empty($path_parts['extension']) ? $path_parts['extension'] : '';
            if(empty($ext)) {
                $type = mime_content_type($file->tmpName);
                if(!empty($type)) {
                    $mimes = explode('/', $type);
                    $ext = end($mimes);
                }
            }

            // Get File Data
            if(!empty($file->data)) {
                $contents = base64_decode($file->data);
            } elseif(!empty($file->tmpName)) {
                $contents = fopen($file->tmpName, 'r+');
            } else {
                $contents = fopen($file->filePath, 'r+');
            }

            // Upload Image
            $key = 'crm/' . $dealerId . '/' . $messageDir . '/attachments/' . $filename . '.' . $ext;
            Storage::disk('s3email')->put($key, $contents, 'public');
            $s3Image = Storage::disk('s3email')->url($_ENV['MAIL_BUCKET'] . '/' . $key);

            // Add Email Attachment
            $attachments[] = [
                'message_id' => $messageId,
                'filename' => $s3Image,
                'original_filename' => $file->name
            ];
        }

        // Return Collection of Attachment
        return $attachments;
    }

    /**
     * Delete Temporary Attachment Files
     * 
     * @param array $files
     */
    private function deleteAttachments($files) {
        // Loop All Attachments
        $deleted = 0;
        if(!empty($files) && count($files) > 0) {
            foreach($files as $file) {
                // Delete Attachments If Exists
                if(!empty($file->tmpName) && file_exists($file->tmpName)) {
                    unlink($file->tmpName);
                    $deleted++;
                }
            }
        }

        // Deleted Some Replies?
        if($deleted > 0) {
            Log::info('Deleted ' . $deleted . ' total temporary attachment files');
        }

        // Return Total
        return $deleted;
    }
}
