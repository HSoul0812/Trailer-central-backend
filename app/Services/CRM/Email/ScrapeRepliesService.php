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
     * @var array
     */
    protected $messageIds = [];
    protected $processed = [];
    protected $leadEmails = [];

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
     * Import Email Replies
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @return false || array of EmailHistory
     */
    public function import($dealer, $salesperson) {
        // Get User Details to Know What to Import
        $this->messageIds = $this->emails->getMessageIds($dealer->user_id);
        $this->processed = $this->emails->getProcessed($dealer->user_id);
        $this->leadEmails = $this->leads->getLeadEmails($dealer->id);

        // Process Messages
        Log::info("Processing Getting Emails for Sales Person #" . $salesperson->id);
        $imported = 0;
        foreach($salesperson->email_folders as $folder) {
            // Try Catching Error for Sales Person Folder
            try {
                // Import Folder
                $imports = $this->importFolder($dealer, $salesperson, $folder);
                Log::info('Imported ' . $imports . ' Email Replies for Sales Person #' .
                            $salesperson->id . ' Folder ' . $folder->name);
                $imported += $imports;
            } catch(\Exception $e) {
                Log::error('Error Importing Sales Person #' .
                            $salesperson->id . ' Folder ' . $folder->name . '; ' .
                            $e->getMessage() . ':' . $e->getTraceAsString());
            }
        }

        // Clean Memory
        unset($this->messageIds);
        unset($this->processed);
        unset($this->leadEmails);

        // Return Campaign Sent Entries
        return $imported;
    }

    /**
     * Import Single Folder
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @param Folder $folder
     * @return false || array of EmailHistory
     */
    private function importFolder($dealer, $salesperson, $folder) {
        // Missing Folder Name?
        if(empty($folder->name)) {
            //$this->updateFolder($folder, false, false);
            return 0;
        }

        // Get From Google?
        if(!empty($salesperson->googleToken)) {
            $replies = $this->importGoogle($salesperson, $folder);
        }
        // Get From IMAP Instead
        else {
            $replies = $this->importImap($salesperson, $folder);
        }

        // Insert Replies Into DB
        $emails = array();
        if(!empty($replies) && count($replies) > 0) {
            foreach($replies as $reply) {
                // Insert Interaction / Email History
                $reply['dealer_id'] = $dealer->id;
                $reply['user_id'] = $salesperson->user_id;
                $emails[] = $this->insertReply($reply);
            }
        }

        // Return Inserted Replies
        return count($emails);
    }

    /**
     * Import Google
     * 
     * @param SalesPerson $salesperson
     * @param EmailFolder $folder
     * @return false || array of email results
     */
    private function importGoogle($salesperson, $folder) {
        // Refresh Token
        $accessToken = $salesperson->googleToken;
        $validate = $this->google->validate($accessToken);
        if(!empty($validate['new_token'])) {
            $accessToken = $this->tokens->refresh($accessToken->id, $validate['new_token']);
        }

        // Get Emails From Google
        $messages = $this->gmail->messages($accessToken, $folder->name);

        // Loop Messages
        $results = array();
        $skipped = array();
        foreach($messages as $k => $message) {
            // Compare Message ID!
            $messageId = $message['headers']['Message-ID'];
            if(count($this->processed) > 0 || count($this->messageIds) > 0) {
                if(in_array($messageId, $this->processed) ||
                   in_array($messageId, $this->messageIds)) {
                    unset($message);
                    unset($messages[$k]);
                    continue;
                }
            }

            // Verify if Email Exists!
            $leadId = 0;
            $direction = 'Received';
            $to = !empty($message['headers']['To']) ? $message['headers']['To'] : '';
            $from = !empty($message['headers']['From']) ? $message['headers']['From'] : '';
            $reply = !empty($message['headers']['Reply-To']) ? $message['headers']['Reply-To'] : '';

            // Get Lead Email Exists?
            if($salesperson->smtp_email !== $to && isset($this->leadEmails[$to])) {
                $leadId = $this->leadEmails[$to];
                $direction = 'Sent';
            } elseif($salesperson->smtp_email !== $from && isset($this->leadEmails[$from])) {
                $leadId = $this->leadEmails[$from];
            } elseif($salesperson->smtp_email !== $reply && isset($this->leadEmails[$reply])) {
                $leadId = $this->leadEmails[$reply];
            }

            // Mark as Skipped
            if(!empty($leadId)) {
                // Get To Name
                $date = strtotime($message['headers']['Date']);
                $subject = $message['headers']['Subject'];
                $toName = $message['headers']['To-Name'];
                $fromName = $message['headers']['From-Name'];

                // Add to Results
                $results[] = [
                    'lead_id' => $leadId,
                    'message_id' => $messageId,
                    'to_email' => $to,
                    'to_name' => !empty($toName) ? $toName : '',
                    'from_email' => $from,
                    'from_name' => !empty($fromName) ? $fromName : '',
                    'subject' => !empty($subject) ? $subject : '',
                    'body' => !empty($message['body']) ? $message['body'] : '',
                    'attachments' => $message['attachments'],
                    'date_sent' => date("Y-m-d H:i:s", $date),
                    'direction' => $direction
                ];
                $this->messageIds[] = $messageId;
            } elseif(!in_array($messageId, $skipped)) {
                $skipped[] = $messageId;
                $this->processed[] = $messageId;
            }
        }

        // Process Skipped Message ID's
        if(count($skipped) > 0) {
            $this->emails->createProcessed($salesperson->user_id, $skipped);
            Log::info("Processed " . count($skipped) . " emails that were skipped and not imported.");
            unset($skipped);
        }

        // Return Result Messages That Match
        return $results;
    }

    /**
     * Import Via Imap
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @param EmailFolder $folder
     * @return false || array of email results
     */
    private function importImap($salesperson, $folder) {
        // Get Emails From IMAP
        $messages = $this->imap->messages($salesperson, $folder);

        // Loop Messages
        $results = array();
        $skipped = array();
        foreach($messages as $k => $parsed) {
            // Compare Message ID!
            if(empty($parsed['subject']) || empty($parsed['message_id']) ||
               count($this->processed) > 0 || count($this->messageIds) > 0) {
                if(empty($parsed['subject']) || empty($parsed['message_id']) ||
                   in_array($parsed['message_id'], $this->processed) ||
                   in_array($parsed['message_id'], $this->messageIds)) {
                    // Delete All Attachments
                    $this->deleteAttachments($parsed['attachments']);
                    unset($parsed);
                    unset($messages[$k]);
                    continue;
                }
            }

            // Verify if Email Exists!
            $leadId = 0;
            $direction = 'Received';
            $to = $parsed['to'];
            $from = $parsed['from'];

            // Get Lead Email Exists?
            if($salesperson->smtp_email !== $to && isset($this->leadEmails[$to])) {
                $leadId = $this->leadEmails[$to];
                $direction = 'Sent';
            } elseif($salesperson->smtp_email !== $from && isset($this->leadEmails[$from])) {
                $leadId = $this->leadEmails[$from];
            }

            // Mark as Skipped
            if(!empty($leadId)) {
                // Add to Results
                $results[] = [
                    'lead_id' => $leadId,
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
                $this->messageIds[] = $parsed['message_id'];
            } else {
                $skipped[] = $parsed['message_id'];
                $this->processed[] = $parsed['message_id'];
            }
            unset($parsed);
            unset($messages[$k]);
        }
        unset($messages);

        // Process Skipped Message ID's
        if(count($skipped) > 0) {
            $this->emails->createProcessed($salesperson->user_id, $skipped);
            Log::info("Processed " . count($skipped) . " emails that were skipped and not imported.");
            unset($skipped);
        }

        // Return Result Messages That Match
        return $results;
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
            $path_parts = pathinfo( $file->filePath );
            $filename = $path_parts['filename'];
            $ext = $path_parts['extension'];

            // Get File Data
            if(!empty($file->data)) {
                $contents = base64_decode($file->data);
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

        // Delete Attachments
        $this->deleteAttachments($files);

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
        foreach($files as $file) {
            // Delete Attachments If Exists
            if(!empty($file->tmpName) && file_exists($file->tmpName)) {
                unlink($file->tmpName);
                $deleted++;
            }
        }

        // Return Total
        Log::info('Deleted ' . $deleted . ' total temporary attachment files');
        return $deleted;
    }
}
