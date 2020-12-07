<?php

namespace App\Services\CRM\Email;

use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;

/**
 * Class ScrapeRepliesService
 * 
 * @package App\Services\CRM\Email
 */
class ImapService implements ImapServiceInterface
{
    /**
     * @var php_imap
     */
    protected $imap;

    /**
     * ScrapeRepliesService constructor.
     */
    public function __construct()
    {
        $this->imap = null;
    }

    /**
     * Import Email Replies
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @return false || array of EmailHistory
     */
    public function import($dealer, $salesperson) {
        // Messages Return?
        $imported = 0;
        if(count($this->messages) > 0 || count($this->leads) > 0) {
            // Process Messages
            echo date("r") . ": " . count($this->messages) . " Sent Emails Found, " .
                count($this->leads) . " Lead Email Addresses Found, " .
                "Processing Getting Emails for User #" . $salesPerson->user_id . PHP_EOL;
            foreach($salesperson->folders as $folder) {
                // Import Folder
                $imported = $this->importFolder($dealer, $salesperson, $folder);
            }
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
            return false;
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
