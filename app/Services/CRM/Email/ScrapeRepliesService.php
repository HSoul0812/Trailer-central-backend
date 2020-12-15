<?php

namespace App\Services\CRM\Email;

use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use App\Services\CRM\Email\ImapServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;

/**
 * Class ScrapeRepliesService
 * 
 * @package App\Services\CRM\Email
 */
class ScrapeRepliesService implements ScrapeRepliesServiceInterface
{
    /**
     * @var App\Services\Integration\Google\GmailServiceInterface
     */
    protected $gmail;

    /**
     * @var App\Services\CRM\Email\ImapServiceInterface
     */
    protected $imap;

    /**
     * ScrapeRepliesService constructor.
     */
    public function __construct(GmailServiceInterface $gmail,
                                ImapServiceInterface $imap,
                                EmailHistoryRepositoryInterface $emails)
    {
        // Initialize Services
        $this->gmail = $gmail;
        $this->imap = $imap;

        // Initialize Repositories
        $this->emails = $emails;
    }

    /**
     * Import Email Replies
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @return false || array of EmailHistory
     */
    public function import($dealer, $salesperson) {
        // Process Messages
        Log::info(count($this->messages) . " Sent Emails Found, " .
            count($this->leads) . " Lead Email Addresses Found, " .
            "Processing Getting Emails for User #" . $salesperson->user_id);
        $imported = 0;
        foreach($salesperson->folders as $folder) {
            // Import Folder
            $imported += $this->importFolder($dealer, $salesperson, $folder);
        }

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
    private function importFolder($salesperson, $folder) {
        // Missing Folder Name?
        if(empty($folder->name)) {
            $this->updateFolder($folder, false, false);
            return 0;
        }

        // Get From Google?
        if(!empty($salesperson->googleToken)) {
            $replies = $this->importGoogle($salesperson->googleToken, $folder);
        }
        // Get From IMAP Instead
        else {
            $replies = $this->importImap($salesperson, $folder);
        }

        // Insert Replies Into DB
        var_dump($replies);

        // Return Inserted Replies
        return $replies;
    }

    /**
     * Import Google
     * 
     * @param AccessToken $accessToken
     * @param EmailFolder $folder
     * @return false || array of EmailHistory
     */
    private function importGoogle($accessToken, $folder) {
        // Get Emails From Google
        $messages = $this->gmail->messages($accessToken, $folder);

        // Loop Messages
        foreach($messages as $message) {
            // Get Headers
            $payload = $message->getPayload();

            // Get Headers
            $headers = $payload->getHeaders();
            var_dump($headers);
        }
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
