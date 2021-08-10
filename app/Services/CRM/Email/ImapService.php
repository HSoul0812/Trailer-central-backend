<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\ImapConnectionFailedException;
use App\Exceptions\CRM\Email\ImapFolderConnectionFailedException;
use App\Exceptions\CRM\Email\ImapFolderUnknownErrorException;
use App\Services\CRM\Email\DTOs\ImapConfig;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Support\MessageCollection;
use Carbon\Carbon;

/**
 * Class ScrapeRepliesService
 *
 * @package App\Services\CRM\Email
 */
class ImapService implements ImapServiceInterface
{
    /**
     * @var PhpImap\Mailbox
     */
    protected $imap;

    /**
     * @var string
     */
    protected $attachmentDir;

    /**
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;

    /**
     * ScrapeRepliesService constructor.
     */
    public function __construct()
    {
        $this->attachmentDir = env('MAIL_ATTACHMENT_DIR');
        if(!file_exists($this->attachmentDir)) {
            mkdir($this->attachmentDir);
        }

        // Initialize Logger
        $this->log = Log::channel('scrapereplies');
    }

    /**
     * Import Email Replies
     *
     * @param ImapConfig $imapConfig
     * @throws App\Exceptions\CRM\Email\ImapConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderUnknownErrorException
     * @return MessageCollection
     */
    public function messages(ImapConfig $imapConfig): MessageCollection {
        // Get IMAP
        $imap = $this->connectIMAP($imapConfig);

        // Error Occurred
        if($imap === null) {
            throw new ImapConnectionFailedException;
        }

        // Return Mailbox
        try {
            // Get Messages
            return $this->getMessages($imapConfig->folderName, $imapConfig->getStartDate());
        } catch (ConnectionException $e) {
            throw new ImapFolderConnectionFailedException($e->getMessage());
        } catch (\Exception $e) {
            throw new ImapFolderUnknownErrorException($e->getMessage());
        }
    }

    /**
     * Get Basic Overview
     *
     * @param Message $overview
     * @return ParsedEmail
     */
    public function overview(Message $overview): ParsedEmail {
        // Get Message ID
        $messageId = $overview->getMessageId();
        $rootMessageId = $overview->getInReplyTo();
        $references = $overview->getReferences();

        // Handle Overriding Message ID From References
        if(!empty($references)) {
            $rootMessageId = reset($references);
            if(empty($messageId)) {
                $messageId = end($references);
            }
        }

        // Return Parsed Array
        $parsed = ParsedEmail([
            'uid' => $overview->getUid(),
            'message_id' => $overview->getMessageId(),
            'subject' => $overview->getSubject(),
            'is_html' => $overview->hasHTMLBody(),
            'body' => $overview->hasHTMLBody() ? $overview->getHTMLBody() : $overview->getTextBody(),
            'date' => $overview->getDate()
        ]);

        // Set To/From
        if(!empty($overview->getTo())) {
            $parsed->setTo($overview->getTo());
        }
        $parsed->setFrom($overview->getFrom());

        // Return Parsed Email
        return $parsed;
    }

    /**
     * Full Reply Details to Clean Up Result
     *
     * @param Message $message
     * @param ParsedEmail $email
     * @return ParsedEmail updated with additional details
     */
    public function full(Message $message, ParsedEmail $email) {
        // Handle Attachments
        $email->setAttachments($this->parseAttachments($message->getAttachments()));
        if(count($email->getAttachments()) > 0) {
            $this->log->info('Found ' . count($email->getAttachments()) . ' total attachments on Message ' . $email->getMessageId());
        }

        // Return Updated ParsedEmail
        return $email;
    }


    /**
     * Connect to IMAP
     *
     * @param string $folder
     * @param array $config
     * @return type
     */
    private function connectIMAP(ImapConfig $imapConfig) {
        // Return Mailbox
        try {
            // Connect to IMAP Server
            $this->log->info('Connecting to IMAP host: ' . $imapConfig->host .
                                ' with email: ' . $imapConfig->username);
            $imap = new Client($imapConfig->getCredentials());
            $imap->connect();
            $this->log->info('Connected to IMAP for email address: ' . $imapConfig->username);
        } catch (\Exception $e) {
            // Logged Exceptions
            $this->imap = null;
            $this->log->error('Cannot connect to ' . $imapConfig->username .
                                ' via IMAP, exception returned: ' . $e->getMessage());
        }

        // Return IMAP Details
        return $this->imap;
    }

    /**
     * Get Messages After Set Date
     *
     * @param string $folderName
     * @param null|string $startTime
     * @param int $days
     * @return MessageCollection
     */
    private function getMessages(string $folderName, ?string $startTime = null, int $days = 7): MessageCollection
    {
        // Get Carbon From Provided Start Time
        if(!empty($startTime)) {
            $since = Carbon::parse($startTime);
        } elseif($days > 0) {
            $since = Carbon::now()->subDays($days);
        }

        // Get Folder
        $folder = $this->imap->getFolder($folderName)->query();

        // Append Since
        if(!empty($since)) {
            $this->log->info('Getting Messages From IMAP Since: "' . $since->toDateTimeString() . '"');
            $folder = $folder->since($since);
        }

        // Return Messages in Time Frame
        $messages = $folder->leaveUnread()->fetchOrderAsc()->get();
        if($messages->count() > 0) {
            $this->log->info('Found ' . $messages->count() . ' Messages to Process');
        }
        return $messages;
    }

    /**
     * Parse Attachments From
     *
     * @param AttachmentCollection $attachments
     * @return Collection<AttachmentFile>
     */
    private function parseAttachments(AttachmentCollection $attachments): Collection {
        // Get Attachments
        $files = [];
        foreach($attachments as $attachment) {
            // Add Files to Array
            $files[] = AttachmentFile::getByImapAttachment($attachment);
        }

        // Return Attachments
        return collect($files);
    }
}
