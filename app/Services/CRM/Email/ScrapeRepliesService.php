<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\MissingAccessTokenImportFolderException;
use App\Exceptions\Common\InvalidEmailCredentialsException;
use App\Exceptions\Common\MissingFolderException;
use App\Jobs\CRM\Email\ScrapeRepliesJob;
use App\Models\CRM\Email\Attachment;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\User\EmailFolder;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\Integration\Auth\AccessToken;
use App\Models\User\NewDealerUser;
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
use App\Services\Integration\Microsoft\OfficeServiceInterface;
use Cache;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webklex\PHPIMAP\Message;
use Carbon\Carbon;

/**
 * Class ScrapeRepliesService
 * 
 * @package App\Services\CRM\Email
 */
class ScrapeRepliesService implements ScrapeRepliesServiceInterface
{
    use DispatchesJobs;

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
     * @var App\Services\Integration\Microsoft\OfficeServiceInterface
     */
    protected $office;

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
                                OfficeServiceInterface $office,
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
        $this->office = $office;
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
        $this->jobLog = Log::channel('scraperepliesjob');
    }


    /**
     * Process Dealer
     * 
     * @param NewDealerUser $dealer
     * @return bool
     */
    public function dealer(NewDealerUser $dealer): bool {
        // Get Salespeople With Email Credentials
        $salespeople = $this->salespeople->getAllImap($dealer->user_id);
        $this->log->info('Dealer #' . $dealer->id . ' Found ' . $salespeople->count() .
                            ' Active Salespeople with IMAP Credentials to Process');
        if($salespeople->count() < 1) {
            return false;
        }

        // Start Time Tracking
        $this->log->info('Found ' . $salespeople->count() . ' Sales People');

        // Loop Campaigns for Current Dealer
        foreach($salespeople as $salesperson) {
            // Try Catching Error for Sales Person
            try {
                // Import Emails
                $job = new ScrapeRepliesJob($dealer, $salesperson);

                // Dispatch ScrapeReplies Job only if there is no pending job
                // for this dealer id and saleperson id
                if ($job->hasNoPending()) {
                    $this->dispatch($job->onQueue('scrapereplies'));
                    $this->log->info('Dealer #' . $dealer->id . ', Sales Person #' .
                                        $salesperson->id . ' - Started Importing Email');

                    // After the job is being dispatched, put it in the cache
                    // so the next command won't create another job until it's finished
                    // we set expiration time to the next two hours to be safe
                    Cache::put($job->cacheKey(), [
                        'created_at' => now(),
                    ], now()->addSeconds(7200));
                } else {
                    $this->log->info('Dealer #' . $dealer->id . ', Sales Person #' .
                                        $salesperson->id . ' - Already Active Job');
                }
            } catch(\Exception $e) {
                $this->log->error('Dealer #' . $dealer->id . ' Sales Person #' .
                                    $salesperson->id . ' - Exception returned: ' . $e->getMessage());
            }
        }

        // End Time Tracking
        $this->log->info('Queued ' . $salespeople->count() . ' Sales People');
        return true;
    }

    /**
     * Process Sales Person
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @return int total number of imported emails
     */
    public function salesperson(NewDealerUser $dealer, SalesPerson $salesperson): int {
        // Start Time Tracking
        $accessToken = $this->getAccessToken($dealer, $salesperson);

        // Process Messages
        $this->jobLog->info('Dealer #' . $dealer->id . ', Sales Person #' . $salesperson->id . 
                            ' - Processing Getting Emails');
        $imported = 0;
        foreach($salesperson->email_folders as $folder) {
            // Try Catching Error for Sales Person Folder
            try {
                // Import Folder
                $imports = $this->folder($dealer, $salesperson, $folder, $accessToken);
                $this->jobLog->info('Dealer #' . $dealer->id . ', Sales Person #' . $salesperson->id . 
                                    ' - Finished Importing ' . $imports .
                                    ' Replies for Folder ' . $folder->name);
                $imported += $imports;
            } catch(\Exception $e) {
                $this->jobLog->error('Dealer #' . $dealer->id . ', Sales Person #' .
                                    $salesperson->id .  ' - Error Importing Folder ' .
                                    $folder->name . ': ' . $e->getMessage());
            }
        }

        // Return Campaign Sent Entries
        $this->jobLog->info('Dealer #' . $dealer->id . ', Sales Person #' . $salesperson->id . 
                            ' - Imported ' . $imported . ' Emails');
        return $imported;
    }

    /**
     * Import Single Folder
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @param Folder $folder
     * @param null|AccessToken $accessToken
     * @return int total number of imported emails
     */
    public function folder(NewDealerUser $dealer, SalesPerson $salesperson,
                            EmailFolder $folder, ?AccessToken $accessToken = null): int {
        // Try Importing
        try {
            // Get From Google?
            if(!empty($accessToken->access_token) && $accessToken->token_type === 'google') {
                $total = $this->importGmail($dealer->id, $salesperson, $accessToken, $folder);
            } elseif(!empty($accessToken->access_token) && $accessToken->token_type === 'office365') {
                // Get From Office 365?
                $total = $this->importOffice($dealer->id, $salesperson, $accessToken, $folder);
            } elseif(!empty($accessToken) && empty($accessToken->access_token)) {
                throw new MissingAccessTokenImportFolderException;
            } else {
                // Get From IMAP Instead
                $total = $this->importImap($dealer->id, $salesperson, $folder);
            }

            // Return Total
            return $total;
        } catch (InvalidEmailCredentialsException $e) {
            //$this->salespeople->update(['id' => $salesperson->id, 'imap_failed' => 1]);
            $this->jobLog->error('Dealer #' . $dealer->id . ', Sales Person #' . $salesperson->id . 
                                ' - Invalid email credentials retrieving email messages: ' .
                                $e->getMessage() . '; marking connection as failed');
        } catch (MissingFolderException $e) {
            $this->folders->delete($folder->folder_id);
            $this->jobLog->error('Dealer #' . $dealer->id . ', Sales Person #' . $salesperson->id . 
                                ' - Folder ' . $folder->name . ' does not exist on ' .
                                'dealer, deleting folder #' . $folder->folder_id);
        } catch (\Exception $e) {
            $this->folders->markFailed($folder->folder_id);
            $this->jobLog->error('Dealer #' . $dealer->id . ', Sales Person #' . $salesperson->id . 
                                ' - Unknown exception thrown retrieving email messages: ' . $e->getMessage());
        }

        // Return Nothing
        return 0;
    }


    /**
     * Import G-Mail
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param AccessToken $accessToken
     * @param EmailFolder $emailFolder
     * @return int total number of imported emails
     */
    private function importGmail(int $dealerId, SalesPerson $salesperson,
                                    AccessToken $accessToken, EmailFolder $emailFolder): int {
        // Get Emails From Gmail
        $this->jobLog->info('Dealer #' . $dealerId . ', Sales Person #' . $salesperson->id . 
                            ' - Connecting to Gmail with Email: ' . $salesperson->smtp_email);
        $messages = $this->gmail->messages($accessToken, $emailFolder->name, [
            'after' => Carbon::parse($emailFolder->date_imported)->subDay()->isoFormat('YYYY/M/D')
        ]);
        $folder = $this->updateFolder($salesperson, $emailFolder);

        // Loop Messages
        $total = $skipped = 0;
        foreach($messages as $mailId) {
            // Get Parsed Message
            $email = $this->gmail->message($mailId);

            // Import Message
            $result = $this->importMessage($dealerId, $salesperson, $email);
            if($result === self::IMPORT_SUCCESS) {
                $total++;
            } elseif($result === self::IMPORT_PROCESSED) {
                $skipped++;
            }
            $this->deleteAttachments($dealerId, $salesperson->id, $email->getAttachments());
        }

        // Process Skipped Message ID's
        if(!empty($skipped)) {
            $this->jobLog->info('Dealer #' . $dealerId . ', Sales Person #' . $salesperson->id . 
                                ' - Processed ' . $skipped . ' emails that were skipped and not imported.');
        }

        // Updated Successful
        $this->folders->markImported($folder->folder_id);

        // Return Result Messages That Match
        return $total;
    }

    /**
     * Import Office 365
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param AccessToken $accessToken
     * @param EmailFolder $emailFolder
     * @return int total number of imported emails
     */
    private function importOffice(int $dealerId, SalesPerson $salesperson, 
                                    AccessToken $accessToken, EmailFolder $emailFolder): int {
        // Get Emails From Gmail
        $this->jobLog->info('Dealer #' . $dealerId . ', Sales Person #' . $salesperson->id . 
                                ' - Connecting to Office 365 with Email: ' . $salesperson->smtp_email);
        $messages = $this->office->messages($accessToken, $emailFolder->name, [
            'SentDateTime ge ' . Carbon::parse($emailFolder->date_imported)->subDay()->isoFormat('YYYY-MM-DD')
        ]);
        $folder = $this->updateFolder($salesperson, $emailFolder);

        // Loop Messages
        $total = $skipped = 0;
        foreach($messages as $message) {
            // Get Parsed Message
            $email = $this->office->message($message);

            // Import Message
            $result = $this->importMessage($dealerId, $salesperson, $email);
            if($result === self::IMPORT_SUCCESS) {
                $total++;
            } elseif($result === self::IMPORT_PROCESSED) {
                $skipped++;
            }
        }

        // Process Skipped Message ID's
        if(!empty($skipped)) {
            $this->jobLog->info('Dealer #' . $dealerId . ', Sales Person #' . $salesperson->id . 
                                ' - Processed ' . $skipped . ' emails that were skipped and not imported.');
        }

        // Updated Successful
        $this->folders->markImported($folder->folder_id);

        // Return Result Messages That Match
        return $total;
    }

    /**
     * Import Via Imap
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param EmailFolder $emailFolder
     * @return int total number of imported emails
     */
    private function importImap(int $dealerId, SalesPerson $salesperson, EmailFolder $emailFolder): int {
        // Get Emails From IMAP
        $this->jobLog->info('Dealer #' . $dealerId . ', Sales Person #' . $salesperson->id . 
                                ' - Connecting to IMAP with Email: ' . $salesperson->imap_email);
        $imapConfig = ImapConfig::fillFromSalesPerson($salesperson, $emailFolder);
        $messages = $this->imap->messages($imapConfig);
        $folder = $this->updateFolder($salesperson, $emailFolder);

        // Loop Messages
        $total = $skipped = 0;
        foreach($messages as $message) {
            // Get Message Overview
            try {
                $email = $this->imap->overview($message);
            } catch (\Exception $e) {
                $this->jobLog->error('Dealer #' . $dealerId . ', Sales Person #' . $salesperson->id . 
                                    ' - Exception thrown retrieving email message: ' . $e->getMessage());
            }
            if(empty($email)) { continue; }

            // Import Message
            $result = $this->importMessage($dealerId, $salesperson, $email, $message);
            if($result === self::IMPORT_SUCCESS) {
                $total++;
            } elseif($result === self::IMPORT_PROCESSED) {
                $skipped++;
            }
        }

        // Process Skipped Message ID's
        if(!empty($skipped)) {
            $this->jobLog->info('Dealer #' . $dealerId . ', Sales Person #' . $salesperson->id . 
                                ' - Processed ' . $skipped . ' emails that were skipped and not imported.');
        }

        // Updated Successful
        $this->folders->markImported($folder->folder_id);

        // Return Result Messages That Match
        return $total;
    }

    /**
     * Import Message From IMAP
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param ParsedEmail $email
     * @param null|Message $message
     * @return int self::IMPORT_SKIPPED | self::IMPORT_PROCESSED | self::IMPORT_SUCCESS
     */
    private function importMessage(int $dealerId, SalesPerson $salesperson, ParsedEmail $email, ?Message $message = null): int {
        // Check if Exists
        if(empty($email->getMessageId()) ||
           $this->emails->findMessageId($salesperson->user_id, $email->getMessageId())) {
            $this->deleteAttachments($dealerId, $salesperson->id, $email->getAttachments());
            return self::IMPORT_SKIPPED;
        }

        // Find Lead
        $this->findLead($dealerId, $salesperson, $email);

        // Lead ID Exists?
        if(!empty($email->getLeadId())) {
            // Import Additional Details (Attachments and/or Body)
            $this->importFull($salesperson, $email, $message);
            if(empty($email->getSubject()) || empty($email->getToEmail())) {
                $this->deleteAttachments($dealerId, $salesperson->id, $email->getAttachments());
                return self::IMPORT_SKIPPED;
            }

            // Get Full IMAP Data
            $this->insertReply($dealerId, $salesperson->user_id, $email);

            // Delete Attachments
            $this->deleteAttachments($dealerId, $salesperson->id, $email->getAttachments());
            return self::IMPORT_SUCCESS;
        }

        // Marked as Processed
        $this->emails->createProcessed($salesperson->user_id, $email->getMessageId());
        $this->deleteAttachments($dealerId, $salesperson->id, $email->getAttachments());
        return self::IMPORT_PROCESSED;
    }

    /**
     * Import Full Details (Attachments/Body) That Weren't Already Imported
     * 
     * @param SalesPerson $salesperson
     * @param ParsedEmail $email
     * @param null|Message $message
     * @return ParsedEmail
     */
    private function importFull(SalesPerson $salesperson, ParsedEmail $email, ?Message $message = null): ParsedEmail {
        // Get From Office 365?
        if(!empty($salesperson->active_token) && $salesperson->active_token->token_type === 'office365') {
            $email = $this->office->full($salesperson->active_token, $email);
        } elseif(empty($salesperson->active_token)) {
            // Get From IMAP Instead
            $email = $this->imap->full($message, $email);
        }

        // Return Updated ParsedEmail
        return $email;
    }

    /**
     * Find Lead That Matches Email
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param ParsedEmail $email
     * @return ParsedEmail with Lead
     */
    private function findLead(int $dealerId, SalesPerson $salesperson, ParsedEmail $email): ParsedEmail {
        // Lookup Lead
        $this->jobLog->info('Dealer #' . $dealerId . ', Sales Person #' . $salesperson->id . 
                            ' - Looking Up Lead for Email From ' . $email->getFromEmail() .
                            ' To ' . $email->getToEmail());

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
     * @return null|EmailHistory
     */
    private function insertReply($dealerId, $userId, $email): ?EmailHistory {
        // Start Transaction
        $emailHistory = null;
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
    private function updateFolder(SalesPerson $salesperson, EmailFolder $folder): EmailFolder {
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
    private function insertAttachments(int $dealerId, string $messageId, Collection $files): Collection {
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
                $this->jobLog->error('Dealer #' . $dealerId . ', Message ID #' . $messageId .
                                    ' - Failed to upload attachment ' . $file->getFileName() . 
                                    ': ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
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
        Storage::disk('s3email')->put($key, $contents);
        return Storage::disk('s3email')->url(env('MAIL_BUCKET') . '/' . $key);
    }

    /**
     * Delete Temporary Attachment Files
     * 
     * @param int $dealerId
     * @param int $salesPersonId
     * @param Collection $files
     * @return int
     */
    private function deleteAttachments(int $dealerId, int $salesPersonId, Collection $files): int {
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
            $this->jobLog->info('Dealer #' . $dealerId . ', Sales Person ID #' . $salesPersonId . 
                                ' - Deleted ' . $deleted . ' Total Temporary Attachment Files');
        }

        // Return Total
        return $deleted;
    }

    /**
     * Validate Token, If Invalid Then Refresh
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @return null|AccessToken
     */
    private function getAccessToken(NewDealerUser $dealer, SalesPerson $salesperson): ?AccessToken {
        // Token Exists?
        if(!empty($salesperson->active_token)) {
            // Refresh Token
            $activeToken = $salesperson->active_token;
            $this->jobLog->info('Dealer #' . $dealer->id . ', Sales Person #' . $salesperson->id . 
                                ' - Validating token #' . $activeToken->id);

            // Try Running OAuth Validate and Refresh Token
            try {
                $validate = $this->auth->validate($activeToken);
            } catch (\Exception $e) {
                //$this->salespeople->update(['id' => $salesperson->id, 'imap_failed' => 1]);
                $this->jobLog->error('Dealer #' . $dealer->id . ', Sales Person #' . $salesperson->id . 
                                    ' - Exception thrown validating active access token: ' .
                                    $e->getMessage() . '; marking connection as failed');
                return $activeToken;
            }

            // Access Token Exists?
            if($validate->accessToken) {
                $accessToken = $validate->accessToken;
                $this->jobLog->info('Dealer #' . $dealer->id . ', Sales Person #' . $salesperson->id . 
                                    ' - Found access token: ' . $accessToken);
            }

            // Return Updated Access Token, Otherwise Return Active Token
            return $accessToken ?? $activeToken;
        }

        // No Access Token
        return null;
    }
}
