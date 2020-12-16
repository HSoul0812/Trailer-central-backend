<?php

namespace App\Services\CRM\Email;

use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\Email\ImapServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use Illuminate\Support\Facades\Log;

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
     * @var App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface
     */
    protected $emails;

    /**
     * ScrapeRepliesService constructor.
     */
    public function __construct(GoogleServiceInterface $google,
                                GmailServiceInterface $gmail,
                                ImapServiceInterface $imap,
                                EmailHistoryRepositoryInterface $emails,
                                TokenRepositoryInterface $tokens)
    {
        // Initialize Services
        $this->google = $google;
        $this->gmail = $gmail;
        $this->imap = $imap;

        // Initialize Repositories
        $this->emails = $emails;
        $this->tokens = $tokens;
    }

    /**
     * Import Email Replies
     * 
     * @param SalesPerson $salesperson
     * @return false || array of EmailHistory
     */
    public function import($dealer, $salesperson) {
        // Get Message ID's and Processed Messages
        $this->messageIds = $this->emails->getMessageIds($dealer->user_id);
        $this->processed = $this->emails->getProcessed($dealer->user_id);

        // Process Messages
        Log::info("Processing Getting Emails for User #" . $salesperson->user_id);
        $imported = 0;
        foreach($salesperson->folders as $folder) {
            // Import Folder
            $imports = $this->importFolder($dealer, $salesperson, $folder);
            Log::info("Imported " . $imports . " Email Replies for Sales Person #" . $salesperson->id);
            $imported += $imports;
        }

        // Clean Memory
        unset($this->messageIds);
        unset($this->processed);

        // Return Campaign Sent Entries
        return $imported;
    }

    /**
     * Import Single Folder
     * 
     * @param SalesPerson $salesperson
     * @param Folder $folder
     * @return false || array of EmailHistory
     */
    private function importFolder($dealer, $salesperson, $folder) {
        // Missing Folder Name?
        if(empty($folder->name)) {
            $this->updateFolder($folder, false, false);
            return 0;
        }

        // Get From Google?
        if(!empty($salesperson->googleToken)) {
            $replies = $this->importGoogle($dealer, $salesperson, $folder);
        }
        // Get From IMAP Instead
        else {
            $replies = $this->importImap($dealer, $salesperson, $folder);
        }

        // Insert Replies Into DB

        // Return Inserted Replies
        return $replies;
    }

    /**
     * Import Google
     * 
     * @param NewDealerUser $dealer
     * @param AccessToken $salesperson
     * @param EmailFolder $folder
     * @return false || array of EmailHistory
     */
    private function importGoogle($dealer, $salesperson, $folder) {
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
            if(in_array($messageId, $this->processed) || in_array($messageId, $this->messageIds)) {
                unset($message);
                unset($messages[$k]);
                continue;
            }

            // Verify if Email Exists!
            $to = $message['headers']['Delivered-To'];
            $from = $message['headers']['From'];
            $reply = $message['headers']['Reply-To'];
            if(($salesperson->smtp_email !== $to && in_array($to, $dealer->leadEmails)) ||
                ($salesperson->smtp_email !== $from && in_array($from, $dealer->leadEmails)) ||
                ($salesperson->smtp_email !== $reply && in_array($reply, $dealer->leadEmails))) {
                // Add to Array
                $results[] = $message;
            } else {
                $skipped[] = $messageId;
            }
        }

        // Process Skipped Message ID's
        $this->emails->createProcessed($salesperson->user_id, $skipped);

        // Return Result Messages That Match
        return $results;
    }

    /**
     * Import Via Imap
     */
    private function importImap($salesperson, $folder) {
        // NTLM?
        if($salesperson->smtp_auth === 'NTLM') {
            $charset = 'US-ASCII';
        }

        
    }
}
