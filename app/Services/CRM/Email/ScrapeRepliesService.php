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
    public function import($salesperson) {
        // Process Messages
        Log::info("Processing Getting Emails for User #" . $salesperson->user_id);
        $imported = 0;
        foreach($salesperson->folders as $folder) {
            // Import Folder
            $imports = $this->importFolder($salesperson, $folder);
            Log::info("Imported " . $imports . " Email Replies for Sales Person #" . $salesperson->id);
            $imported += $imports;
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
        // Refresh Token
        $validate = $this->google->validate($accessToken);
        if(!empty($validate['new_token'])) {
            $accessToken = $this->tokens->refresh($accessToken->id, $validate['new_token']);
        }

        // Get Emails From Google
        $messages = $this->gmail->messages($accessToken, $folder->name);

        // Loop Messages
        foreach($messages as $message) {
            // Get Headers
            $payload = $message->getPayload();

            // Get Headers
            var_dump($message);
            var_dump($payload);
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
