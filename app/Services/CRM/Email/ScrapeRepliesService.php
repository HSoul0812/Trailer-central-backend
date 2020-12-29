<?php

namespace App\Services\CRM\Email;

use App\Models\User\NewDealerUser;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\User\EmailFolder;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\CRM\User\EmailFolderRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\Email\ImapServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
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
     * ScrapeRepliesService constructor.
     */
    public function __construct(GoogleServiceInterface $google,
                                GmailServiceInterface $gmail,
                                ImapServiceInterface $imap,
                                InteractionsRepositoryInterface $interactions,
                                EmailHistoryRepositoryInterface $emails,
                                SalesPersonRepositoryInterface $salesRepo,
                                EmailFolderRepositoryInterface $folders,
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
        $this->salespeople = $salesRepo;
        $this->folders = $folders;
        $this->tokens = $tokens;
        $this->leads = $leads;
    }


    /**
     * Process Dealer
     * 
     * @param User $dealer
     * @return int total number of imported emails
     */
    public function dealer(NewDealerUser $dealer) {
        // Get Salespeople With Email Credentials
        $salespeople = $this->salespeople->getAllImap($dealer->user_id);
        if(count($salespeople) < 1) {
            return false;
        }

        // Loop Campaigns for Current Dealer
        $imported = 0;
        Log::info("Dealer #{$dealer->id} Found " . count($salespeople) . " Active Salespeople with IMAP Credentials to Process");
        foreach($salespeople as $salesperson) {
            // Try Catching Error for Sales Person
            try {
                // Import Emails
                Log::info("Importing Emails on Sales Person #{$salesperson->id} for Dealer #{$dealer->id}");
                $imports = $this->salesperson($dealer, $salesperson);

                // Adjust Total Import Counts
                Log::info("Imported {$imports} Emails on Sales Person #{$salesperson->id}");
                $imported += $imports;
            } catch(\Exception $e) {
                Log::error("Exception returned on Sales Person #{$salesperson->id} {$e->getMessage()}: {$e->getTraceAsString()}");
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
        if(!empty($salesPerson->googleToken)) {
            // Refresh Token
            $validate = $this->google->validate($salesPerson->googleToken);
            if(!empty($validate['new_token'])) {
                $accessToken = $this->tokens->refresh($accessToken->id, $validate['new_token']);
                $salesPerson->setRelation('googleToken', $accessToken);
            }
        }

        // Process Messages
        Log::info('Processing Getting Emails for Sales Person #' . $salesperson->id);
        $imported = 0;
        foreach($salesperson->email_folders as $folder) {
            // Try Catching Error for Sales Person Folder
            try {
                // Import Folder
                $imports = $this->folder($dealer, $salesperson, $folder);
                Log::info('Imported ' . $imports . ' Email Replies for Sales Person #' .
                            $salesperson->id . ' Folder ' . $folder->name);
                $imported += $imports;
            } catch(\Exception $e) {
                Log::error('Error Importing Sales Person #' .
                            $salesperson->id . ' Folder ' . $folder->name . '; ' .
                            $e->getMessage() . ':' . $e->getTraceAsString());
            }
        }

        // Return Campaign Sent Entries
        return $imported;
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
            if(!empty($salesperson->googleToken)) {
                $total = $this->importGmail($dealer->id, $salesperson, $folder);
            }
            // Get From IMAP Instead
            else {
                $total = $this->importImap($dealer->id, $salesperson, $folder);
            }
        } catch (\Exception $e) {
            $this->folders->markFailed($folder->folder_id);
            Log::error('Failed to Connect to Sales Person #' . $salesperson->id .
                        ' Folder ' . $folder->name . '; exception returned: ' .
                        $e->getMessage() . ': ' . $e->getTraceAsString());
            return 0;
        }

        // Return Inserted Replies
        return $total;
    }


    /**
     * Import G-Mail
     * 
     * @param int $dealerId
     * @param SalesPerson $salesperson
     * @param EmailFolder $folder
     * @return false || array of email results
     */
    private function importGmail($dealerId, $salesperson, $folder) {
        // Get Emails From Gmail
        $messages = $this->gmail->messages($salesperson->googleToken, $folder->name, ['after' => $folder->date_imported]);

        // Update Folder
        $folder = $this->updateFolder($salesperson, $folder);

        // Loop Messages
        $total = 0;
        $skipped = 0;
        $imported = '';
        foreach($messages as $overview) {
            // Get Parsed Message
            $params = $this->gmail->message($overview);
            if(empty($imported) || (!empty($imported) && strtotime($imported) < strtotime($params['date_sent']))) {
                $imported = $params['date_sent'];
            }

            // Check if Exists
            if(empty($params['subject']) || empty($params['message_id']) ||
               $this->emails->findMessageId($salesperson->user_id, $params['message_id'])) {
                // Delete All Attachments
                Log::info('Already Processed Email Message ' . $params['message_id']);
                $this->deleteAttachments($params['attachments']);
                continue;
            }

            // Get Lead Email Exists?
            $emails = [];
            $direction = 'Received';
            if($salesperson->smtp_email !== $params['to_email']) {
                $emails[] = $params['to_email'];
                $direction = 'Sent';
            }
            if($salesperson->smtp_email !== $params['from_email']) {
                $emails[] = $params['from_email'];
            }
            $lead = $this->leads->getByEmails($dealerId, $emails);

            // Valid Lead
            if(!empty($lead->identifier)) {
                // Get To Name
                $params['dealer_id'] = $dealerId;
                $params['user_id'] = $salesperson->user_id;
                $params['lead_id'] = $lead->identifier;
                $params['direction'] = $direction;
                $message = $this->insertReply($params);

                // Valid?
                if(!empty($message->message_id)) {
                    $total++;
                    Log::info("Inserted Email Message " . $message->message_id);
                }
            }
            // Mark as Skipped
            else {
                $this->emails->createProcessed($salesperson->user_id, $params['message_id']);
                $skipped++;
                Log::info("Skipped Email Message " . $params['message_id']);
            }

            // Clear Memory/Space
            $this->deleteAttachments($params['attachments']);
        }

        // Process Skipped Message ID's
        if($skipped > 0) {
            Log::info("Processed " . $skipped . " emails that were skipped and not imported.");
        }

        // Updated Successful
        $this->folders->update([
            'id' => $folder->folder_id,
            'date_imported' => !empty($imported) ? $imported : Carbon::now()
        ]);

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

        // Update Folder
        $folder = $this->updateFolder($salesperson, $folder);

        // Loop Messages
        $total = 0;
        $skipped = 0;
        $imported = '';
        foreach($messages as $mailId) {
            // Get Message Overview
            $overview = $this->imap->overview($mailId);
            if(empty($imported) || (!empty($imported) && strtotime($imported) < strtotime($overview['date_sent']))) {
                $imported = $overview['date_sent'];
            }

            // Check if Exists
            if(empty($overview['subject']) || empty($overview['message_id']) ||
               $this->emails->findMessageId($salesperson->user_id, $overview['message_id'])) {
                // Delete All Attachments
                Log::info('Already Processed Email Message ' . $overview['message_id']);
                continue;
            }

            // Get Lead Email Exists?
            $emails = [];
            $direction = 'Received';
            if($salesperson->smtp_email !== $overview['to_email']) {
                $emails[] = $overview['to_email'];
                $direction = 'Sent';
            }
            if($salesperson->smtp_email !== $overview['from_email']) {
                $emails[] = $overview['from_email'];
            }
            $lead = $this->leads->getByEmails($dealerId, $emails);

            // Valid Lead
            if(!empty($lead->identifier)) {
                // Get Full IMAP Data
                $params = $this->imap->parsed($overview);
                $params['dealer_id'] = $dealerId;
                $params['user_id'] = $salesperson->user_id;
                $params['lead_id'] = $lead->identifier;
                $params['direction'] = $direction;
                $message = $this->insertReply($params);

                // Valid?
                if(!empty($message->message_id)) {
                    $total++;
                    Log::info("Inserted Email Message " . $message->message_id);
                }

                // Clear Memory/Space
                $this->deleteAttachments($params['attachments']);
                $messageId = $params['message_id'];
                Log::info('Cleared Email Message ' . $messageId);
            }
            // Mark as Skipped
            else {
                $this->emails->createProcessed($salesperson->user_id, $overview['message_id']);
                $skipped++;
                Log::info("Skipped Email Message " . $overview['message_id']);
            }
        }

        // Process Skipped Message ID's
        if($skipped > 0) {
            Log::info("Processed " . $skipped . " emails that were skipped and not imported.");
        }

        // Updated Successful
        $this->folders->update([
            'id' => $folder->folder_id,
            'date_imported' => !empty($imported) ? $imported : Carbon::now()
        ]);

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
        });

        // Return Final Email
        return $email;
    }

    /**
     * Update Folder With Sales Person and Folder Details
     * 
     * @param SalesPerson $salesperson
     * @param EmailFolder $folder
     * @return EmailFolder
     */
    private function updateFolder($salesperson, $folder) {
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
