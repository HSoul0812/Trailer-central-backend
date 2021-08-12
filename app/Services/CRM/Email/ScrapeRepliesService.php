<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\MissingImapFolderException;
use App\Exceptions\Integration\Google\MissingGmailLabelException;
use App\Models\User\NewDealerUser;
use App\Models\CRM\Email\Attachment;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\User\EmailFolder;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\CRM\User\EmailFolderRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\Email\ImapServiceInterface;
use App\Services\CRM\Email\DTOs\ImapConfig;
use App\Services\Integration\AuthServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GmailServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Class ScrapeRepliesService
 * 
 * @package App\Services\CRM\Email
 */
class ScrapeRepliesService implements ScrapeRepliesServiceInterface
{
    /**
     * @const int
     */
    const IMPORT_SUCCESS = 1;
    const IMPORT_PROCESSED = 0;
    const IMPORT_SKIPPED = -1;

    /**
     * @var App\Services\Integration\Google\GmailServiceInterface
     */
    protected $gmail;

    /**
     * @var App\Services\Integration\Google\AuthServiceInterface
     */
    protected $auth;

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
     * @var App\Repositories\CRM\User\SalesPersonRepositoryInterface
     */
    protected $salespeople;

    /**
     * @var App\Repositories\CRM\User\EmailFolderRepositoryInterface
     */
    protected $folders;

    /**
     * @var App\Repositories\Integration\Auth\TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * @var App\Repositories\CRM\Leads\LeadRepositoryInterface
     */
    protected $leads;

    /**
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;

    /**
     * ScrapeRepliesService constructor.
     */
    public function __construct(GmailServiceInterface $gmail,
                                AuthServiceInterface $auth,
                                ImapServiceInterface $imap,
                                InteractionsRepositoryInterface $interactions,
                                EmailHistoryRepositoryInterface $emails,
                                SalesPersonRepositoryInterface $salesRepo,
                                EmailFolderRepositoryInterface $folders,
                                TokenRepositoryInterface $tokens,
                                LeadRepositoryInterface $leads)
    {
        // Initialize Services
        $this->gmail = $gmail;
        $this->auth = $auth;
        $this->imap = $imap;

        // Initialize Repositories
        $this->interactions = $interactions;
        $this->emails = $emails;
        $this->salespeople = $salesRepo;
        $this->folders = $folders;
        $this->tokens = $tokens;
        $this->leads = $leads;

        // Initialize Logger
        $this->log = Log::channel('scrapereplies');
    }


    /**
     * Process Dealer
     * 
     * @param User $dealer
     * @return int total number of imported emails
     */
    public function dealer(NewDealerUser $dealer): int {
        // Get Salespeople With Email Credentials
        $salespeople = $this->salespeople->getAllImap($dealer->user_id);
        $this->log->info('Dealer #' . $dealer->id . ' Found ' . $salespeople->count() .
                            ' Active Salespeople with IMAP Credentials to Process');
        if($salespeople->count() < 1) {
            return false;
        }

        // Loop Campaigns for Current Dealer
        $imported = 0;
        foreach($salespeople as $salesperson) {
            // Try Catching Error for Sales Person
            try {
                // Import Emails
                $this->log->info('Importing Emails on Sales Person #' . $salesperson->id .
                                    ' for Dealer #' . $dealer->id);
                $imports = $this->salesperson($dealer, $salesperson);

                // Adjust Total Import Counts
                $this->log->info('Imported ' . $imports . ' Emails on Sales Person #' . $salesperson->id);
                $imported += $imports;
            } catch(\Exception $e) {
                $this->log->error('Exception returned on Sales Person #' . $salesperson->id . ': ' . $e->getMessage());
            }
        }

        // Return Imported Email Count for Dealer
        return $imported;
    }

    /**
     * Process Sales Person
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @return int total number of imported emails
     */
    public function salesperson(NewDealerUser $dealer, SalesPerson $salesperson) {
        // Token Exists?
        if(!empty($salesperson->active_token)) {
            // Refresh Token
            $this->log->info('Validating token #' . $salesperson->active_token->id);
            $this->auth->validate($salesperson->active_token);
        }

        // Process Messages
        $this->log->info('Processing Getting Emails for Sales Person #' . $salesperson->id);
        foreach($salesperson->email_folders as $folder) {
            // Try Catching Error for Sales Person Folder
            try {
                // Import Folder
                $imports = $this->folder($dealer, $salesperson, $folder);
                $this->log->info('Imported ' . $imports . ' Email Replies for Sales Person #' .
                            $salesperson->id . ' Folder ' . $folder->name);
                $imported = ($imported ?? 0) + $imports;
            } catch(\Exception $e) {
                $this->log->error('Error Importing Sales Person #' .
                            $salesperson->id . ' Folder ' . $folder->name . '; ' .
                            $e->getMessage() . ':' . $e->getTraceAsString());
            }
        }

        // Return Campaign Sent Entries
        return $imported ?? 0;
    }

    /**
     * Import Single Folder
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @param Folder $folder
     * @return int total number of imported emails
     */
    public function folder(NewDealerUser $dealer, SalesPerson $salesperson, EmailFolder $folder) {
        // Try Importing
        try {
            // Get From Google?
            if(!empty($salesperson->active_token) && $salesperson->active_token->token_type === 'google') {
                $total = $this->importGmail($dealer->id, $salesperson, $folder);
            } else {
                // Get From IMAP Instead
                $total = $this->importImap($dealer->id, $salesperson, $folder);
            }

            // Return Total
            return $total;
        } catch (MissingGmailLabelException $e) {
            $this->folders->delete($folder->folder_id);
        } catch (MissingImapFolderException $e) {
            $this->folders->delete($folder->folder_id);
        } catch (\Exception $e) {
            $this->folders->markFailed($folder->folder_id);
        }

        // Return Nothing
        $this->log->error('Failed to Connect to Sales Person #' . $salesperson->id .
                    ' Folder ' . $folder->name . '; exception returned: ' . $e->getMessage());
        return 0;
    }


    /**
     * Import G-Mail
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param EmailFolder $emailFolder
     * @return false || array of email results
     */
    private function importGmail(int $dealerId, SalesPerson $salesperson, EmailFolder $emailFolder) {
        // Get Emails From Gmail
        $this->log->info("Connecting to Gmail with email: " . $salesperson->smtp_email);
        $messages = $this->gmail->messages($salesperson->active_token, $emailFolder->name, [
            'after' => Carbon::parse($emailFolder->date_imported)->isoFormat('YYYY/M/D')
        ]);
        $folder = $this->updateFolder($salesperson, $emailFolder);

        // Loop Messages
        $total = 0;
        $skipped = 0;
        foreach($messages as $mailId) {
            // Get Parsed Message
            $email = $this->gmail->message($mailId);

            // Import Message
            $result = $this->importMessage($dealerId, $salesperson, $email);
            if($result === 1) {
                $total++;
            } elseif($result === 0) {
                $skipped++;
            }
            $this->deleteAttachments($email->getAttachments());
        }

        // Process Skipped Message ID's
        if($skipped > 0) {
            $this->log->info("Processed " . $skipped . " emails that were skipped and not imported.");
        }

        // Updated Successful
        $this->folders->update([
            'id' => $folder->folder_id,
            'date_imported' => Carbon::now()
        ]);

        // Return Result Messages That Match
        return $total;
    }

    /**
     * Import Via Imap
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param EmailFolder $emailFolder
     * @return false || array of email results
     */
    private function importImap(int $dealerId, SalesPerson $salesperson, EmailFolder $emailFolder) {
        // Get Emails From IMAP
        $imapConfig = ImapConfig::fillFromSalesPerson($salesperson, $emailFolder);
        $messages = $this->imap->messages($imapConfig);
        $folder = $this->updateFolder($salesperson, $emailFolder);

        // Loop Messages
        foreach($messages as $message) {
            // Get Message Overview
            $email = $this->imap->overview($message);
            if(empty($email)) { continue; }

            // Import Message
            $result = $this->importMessage($dealerId, $salesperson, $email);
            if($result === self::IMPORT_SUCCESS) {
                $total = ($total ?? 0) + 1;
            } elseif($result === self::IMPORT_PROCESSED) {
                $skipped = ($skipped ?? 0) + 1;
            }
        }

        // Process Skipped Message ID's
        if(!empty($skipped)) {
            $this->log->info('Processed ' . $skipped . ' emails that were skipped and not imported.');
        }

        // Updated Successful
        $this->folders->markImported($folder->folder_id);

        // Return Result Messages That Match
        return $total ?? 0;
    }

    /**
     * Import Message From IMAP
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param ParsedEmail $email
     * @return int self::IMPORT_SKIPPED | self::IMPORT_PROCESSED | self::IMPORT_SUCCESS
     */
    private function importMessage(int $dealerId, SalesPerson $salesperson, ParsedEmail $email): int {
        // Check if Exists
        if(empty($email->getMessageId()) ||
           $this->emails->findMessageId($salesperson->user_id, $email->getMessageId())) {
            $this->deleteAttachments($email->getAttachments());
            return self::IMPORT_SKIPPED;
        }

        // Find Lead
        $this->findLead($dealerId, $salesperson, $email);

        // Lead ID Exists?
        if(!empty($email->getLeadId())) {
            // Only on IMAP
            if(empty($salesperson->active_token)) {
                $this->imap->full($email);
            }
            if(empty($email->getSubject()) || empty($email->getToEmail())) {
                $this->deleteAttachments($email->getAttachments());
                return self::IMPORT_SKIPPED;
            }

            // Get Full IMAP Data
            $this->insertReply($dealerId, $salesperson->user_id, $email);

            // Delete Attachments
            $this->deleteAttachments($email->getAttachments());
            return self::IMPORT_SUCCESS;
        }

        // Marked as Processed
        $this->emails->createProcessed($salesperson->user_id, $email->getMessageId());
        $this->deleteAttachments($email->getAttachments());
        return self::IMPORT_PROCESSED;
    }

    /**
     * Find Lead That Matches Email
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param ParsedEmail $email
     * @return Lead
     */
    private function findLead(int $dealerId, SalesPerson $salesperson, ParsedEmail $email) {
        // Get Emails
        $emails = [];
        if($salesperson->smtp_email !== $email->getToEmail() &&
           $salesperson->imap_email !== $email->getToEmail()) {
            $emails[] = $email->getToEmail();
            $email->setDirection('Sent');
        }
        if($salesperson->smtp_email !== $email->getFromEmail() &&
           $salesperson->imap_email !== $email->getFromEmail()) {
            $emails[] = $email->getFromEmail();
        }

        // Get Lead By Emails
        $lead = $this->leads->getByEmails($dealerId, $emails);
        if(!empty($lead->identifier)) {
            $email->setLeadId($lead->identifier);
        }

        // Return
        return $email;
    }

    /**
     * Insert Reply Into DB
     * 
     * @param int $dealerId
     * @param int $userId
     * @param ParsedEmail $email
     * @return array
     */
    private function insertReply($dealerId, $userId, $email) {
        // Start Transaction
        $emailHistory = [];
        DB::transaction(function() use (&$emailHistory, $dealerId, $userId, $email) {
            // Insert Interaction
            $interaction = $this->interactions->create([
                'lead_id' => $email->getLeadId(),
                'user_id' => $userId,
                'interaction_type' => 'EMAIL',
                'interaction_notes' => 'E-Mail ' . $email->getDirection() . ': ' . $email->getSubject(),
                'interaction_time' => $email->getDate()
            ]);

            // Insert Attachments
            $this->insertAttachments($dealerId, $email->getMessageId(), $email->getAttachments());

            // Insert Email History Entry
            $emailHistory = $this->emails->create([
                'lead_id' => $email->getLeadId(),
                'interaction_id' => $interaction->interaction_id,
                'message_id' => $email->getMessageId(),
                'root_message_id' => $email->getRootMessageId(),
                'to_email' => $email->getToEmail(),
                'to_name' => $email->getToName(),
                'from_email' => $email->getFromName(),
                'subject' => $email->getSubject(),
                'body' => $email->getBody(),
                'use_html' => $email->getIsHtml(),
                'date_sent' => $email->getDate()
            ]);
        });

        // Return Final Email
        return $emailHistory;
    }

    /**
     * Update Folder With Sales Person and Folder Details
     * 
     * @param SalesPerson $salesperson
     * @param EmailFolder $folder
     * @return EmailFolder
     */
    private function updateFolder(SalesPerson $salesperson, EmailFolder $folder) {
        // Create or Update Folder
        return $this->folders->createOrUpdate([
            'id' => $folder->folder_id,
            'sales_person_id' => $salesperson->id,
            'user_id' => $salesperson->user_id,
            'name' => $folder->name,
            'failures_since' => 0,
            'deleted' => 0,
            'error' => 0
        ]);
    }


    /**
     * Insert Attachments
     * 
     * @param int $dealerId
     * @param string $messageId
     * @param Collection<AttachmentFile>
     * @return Collection<Attachment>
     */
    private function insertAttachments(int $dealerId, string $messageId, Collection $files) {
        // Loop Attachments
        $attachments = [];
        foreach($files as $file) {
            // Skip Entry
            if(empty($file->getFilePath())) {
                continue;
            }

            // Upload File
            try {
                $s3Image = $this->uploadAttachment($dealerId, $messageId, $file);

                // Add Attachments to Array
                $attachments[] = Attachment::create([
                    'message_id' => $messageId,
                    'filename' => $s3Image,
                    'original_filename' => $file->getFileName()
                ]);
            } catch(\Exception $e) {
                $this->log->error("Exception returned uploading attachment {$file->getFileName()} on Message ID #{$messageId}; {$e->getMessage()}: {$e->getTraceAsString()}");
            }
        }

        // Return Collection of Attachment
        return collect($attachments);
    }

    /**
     * Upload Attachment
     * 
     * @param int $dealerId
     * @param string $messageId
     * @param AttachmentFile $file
     * @return string
     */
    private function uploadAttachment($dealerId, $messageId, $file) {
        // Upload File to S3
        $messageDir = str_replace(">", "", str_replace("<", "", $messageId));
        $path_parts = pathinfo( $file->getFileName() );
        $filename = $path_parts['filename'];
        $ext = !empty($path_parts['extension']) ? $path_parts['extension'] : '';
        if(empty($ext)) {
            $ext = $file->getMimeExt();
        }

        // Get File Data
        if(!empty($file->getContents())) {
            $contents = base64_decode($file->getContents());
        } elseif(!empty($file->getTmpName())) {
            $contents = fopen($file->getTmpName(), 'r+');
        } else {
            $contents = fopen($file->getFilePath(), 'r+');
        }

        // Upload Image
        $key = 'crm/' . $dealerId . '/' . $messageDir . '/attachments/' . $filename . '.' . $ext;
        Storage::disk('s3email')->put($key, $contents, 'public');
        return Storage::disk('s3email')->url(env('MAIL_BUCKET') . '/' . $key);
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
                if(!empty($file->getTmpName()) && file_exists($file->getTmpName())) {
                    unlink($file->getTmpName());
                    $deleted++;
                }
            }
        }

        // Deleted Some Replies?
        if($deleted > 0) {
            $this->log->info('Deleted ' . $deleted . ' Total Temporary Attachment Files');
        }

        // Return Total
        return $deleted;
    }
}
