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
        $this->leadEmails = $this->leads->getLeadEmails($dealer->user_id);

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
            $this->updateFolder($folder, false, false);
            return 0;
        }

        // Get From Google?
        if(!empty($salesperson->googleToken)) {
            $replies = $this->importGoogle($salesperson, $folder);
        }
        // Get From IMAP Instead
        else {
            $replies = $this->importImap($dealer, $salesperson, $folder);
        }

        // Insert Replies Into DB
        $emails = array();
        if(!empty($replies) && count($replies) > 0) {
            foreach($replies as $reply) {
                // Interaction Exists?
                if(empty($interactionId)) {
                    // Insert Interaction
                    $interaction = $this->interactions->create([
                        'tc_lead_id' => $reply['lead_id'],
                        'user_id' => $salesperson->user_id,
                        'interaction_type' => 'EMAIL',
                        'interaction_notes' => 'E-Mail ' . $reply['direction'] . ': ' . $reply['subject'],
                        'interaction_time' => $reply['date_sent']
                    ]);
                } else {
                    // Update Interaction
                    $interaction = $this->interactions->update([
                        'id' => $interactionId,
                        'interaction_type' => 'EMAIL',
                        'interaction_notes' => 'E-Mail ' . $reply['direction'] . ': ' . $reply['subject'],
                        'interaction_time' => $reply['date_sent']
                    ]);
                }

                // Insert Email History Entry
                unset($reply['direction']);
                $reply['interaction_id'] = $interaction->interaction_id;
                $emails[] = $this->emails->create($reply);
            }
        }

        // Return Inserted Replies
        return collect($emails);
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
            if(in_array($messageId, $this->processed) || in_array($messageId, $this->messageIds)) {
                unset($message);
                unset($messages[$k]);
                continue;
            }

            // Verify if Email Exists!
            $leadId = 0;
            $direction = 'Received';
            $to = $message['headers']['Delivered-To'];
            $from = $message['headers']['From'];
            $reply = $message['headers']['Reply-To'];

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
                $subject = $message['headers']['Subject'];
                $toName = $message['headers']['Delivered-To-Name'];
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
                    'body' => base64_decode($message['body']),
                    'direction' => $direction
                ];
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
     * 
     * @param NewDealerUser $dealer
     * @param SalesPerson $salesperson
     * @param EmailFolder $folder
     * @return false || array of email results
     */
    private function importImap($salesperson, $folder) {
        // Get Emails From IMAP
        $messages = $this->imap->messages($salesperson, $folder->name);

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
}
