<?php

namespace App\Services\CRM\Email;

use App\Exceptions\Common\MissingFolderException;
use App\Exceptions\CRM\Email\ImapConnectionFailedException;
use App\Exceptions\CRM\Email\ImapFolderConnectionFailedException;
use App\Exceptions\CRM\Email\ImapFolderUnknownErrorException;
use App\Exceptions\CRM\Email\ImapMailboxesMissingException;
use App\Exceptions\CRM\Email\ImapMailboxesErrorException;
use App\Models\CRM\User\SalesPerson;
use App\Services\CRM\Email\DTOs\ConfigValidate;
use App\Services\CRM\Email\DTOs\ImapConfig;
use App\Services\CRM\Email\DTOs\ImapMailbox;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Support\MessageCollection;
use Webklex\PHPIMAP\Support\AttachmentCollection;
use Webklex\PHPIMAP\Exceptions\ConnectionFailedException;
use Carbon\Carbon;

/**
 * Class ScrapeRepliesService
 *
 * @package App\Services\CRM\Email
 */
class ImapService implements ImapServiceInterface
{
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
        $this->attachmentDir = config('mail.attachments.dir');
        if(!file_exists($this->attachmentDir)) {
            mkdir($this->attachmentDir);
        }

        // Initialize Logger
        $this->log = Log::channel('imap');
    }

    /**
     * Validate Imap
     *
     * @param ImapConfig $imapConfig
     * @return ConfigValidate
     */
    public function validate(ImapConfig $imapConfig): ConfigValidate {
        // Get Mailboxes
        try {
            $mailboxes = $this->mailboxes($imapConfig);
            return new ConfigValidate([
                'type' => SalesPerson::TYPE_IMAP,
                'success' => true,
                'folders' => $mailboxes
            ]);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
        }

        // Verify We Can Get Messages Without Errors Instead
        try {
            // No Mailboxes Returned?
            $imapConfig->setFolderName(ImapConfig::FOLDER_INBOX);
            $this->messages($imapConfig);

            // Return ConfigValidate
            return new ConfigValidate([
                'type' => SalesPerson::TYPE_IMAP,
                'success' => true
            ]);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage());
        }

        // Return ConfigValidate
        return new ConfigValidate([
            'type' => SalesPerson::TYPE_IMAP,
            'success' => false
        ]);
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
            $this->log->error('Failed to connect to IMAP using config: ' . print_r($imapConfig, true));
            throw new ImapConnectionFailedException;
        }

        // Return Mailbox
        try {
            // Get Messages
            return $this->getMessages($imap, $imapConfig->folderName, $imapConfig->getStartDate());
        } catch (MissingFolderException $e) {
            throw new MissingFolderException;
        } catch (ConnectionException $e) {
            $this->log->error('IMAP threw an exception: ' . $e->getMessage() . PHP_EOL .
                                'Trace: ' . $e->getPrevious()->getTraceAsString());
            throw new ImapFolderConnectionFailedException($e->getMessage());
        } catch (\Exception $e) {
            $this->log->error('Unknown Exception thrown while handling IMAP: ' . $e->getMessage() . PHP_EOL .
                                'Trace: ' . $e->getPrevious()->getTraceAsString());
            throw new ImapFolderUnknownErrorException($e->getMessage());
        }
    }

    /**
     * Import Mailboxes
     *
     * @param ImapConfig $imapConfig
     * @throws App\Exceptions\CRM\Email\ImapConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderUnknownErrorException
     * @return Collection<ImapMailbox>
     */
    public function mailboxes(ImapConfig $imapConfig): Collection {
        // Get IMAP
        $imap = $this->connectIMAP($imapConfig);

        // Error Occurred
        if($imap === null) {
            $this->log->error('Failed to connect to IMAP using config: ' . print_r($imapConfig, true));
            throw new ImapConnectionFailedException;
        }

        // Return Mailbox
        try {
            // Get Messages
            return $this->getMailboxes($imap);
        } catch (ConnectionException $e) {
            $this->log->error('IMAP threw an exception: ' . $e->getMessage() . PHP_EOL .
                                'Trace: ' . $e->getPrevious()->getTraceAsString());
            throw new ImapMailboxesMissingException($e->getMessage());
        } catch (\Exception $e) {
            $this->log->error('Unknown exception thrown while handling IMAP mailboxes: ' . $e->getMessage() . PHP_EOL .
                                'Trace: ' . $e->getPrevious()->getTraceAsString());
            throw new ImapMailboxesErrorException($e->getMessage());
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
        $parsed = new ParsedEmail([
            'uid' => $overview->getSequenceId(),
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
    public function full(Message $message, ParsedEmail $email): ParsedEmail {
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
     * @return null|Client
     */
    private function connectIMAP(ImapConfig $imapConfig): ?Client {
        // Return Mailbox
        try {
            // Connect to IMAP Server
            $this->log->info('Connecting to IMAP host: ' . $imapConfig->getHost() .
                                ' with email: ' . $imapConfig->username);
            $this->log->info('Fixed returning IMAP credentials: ' . print_r($imapConfig->getCredentials(), true));
            $client = new ClientManager();
            $imap = $client->make($imapConfig->getCredentials());
            $imap->connect();
            $this->log->info('Connected to IMAP for email address: ' . $imapConfig->username);
        } catch (ConnectionFailedException $e) {
            // Logged Exceptions
            $imap = null;
            $this->log->error('Cannot connect to ' . $imapConfig->username . ' via IMAP, ' .
                                'exception returned: ' . $e->getMessage() . PHP_EOL);
        } catch (\Exception $e) {
            // Logged Exceptions
            $imap = null;
            $this->log->error('Cannot connect to ' . $imapConfig->username . ' via IMAP, ' .
                                'exception returned: ' . $e->getMessage());
        }

        // Return IMAP Details
        return $imap;
    }

    /**
     * Get Messages After Set Date
     *
     * @param Client $imap
     * @param string $folderName
     * @param null|string $startTime
     * @param int $days
     * @return MessageCollection
     */
    private function getMessages(Client $imap, string $folderName, ?string $startTime = null, int $days = 7): MessageCollection
    {
        // Get Carbon From Provided Start Time
        if(!empty($startTime)) {
            $since = Carbon::parse($startTime);
        } elseif($days > 0) {
            $since = Carbon::now()->subDays($days);
        }

        // Get Folder
        $folder = $imap->getFolder($folderName);
        if($folder === null) {
            throw new MissingFolderException;
        }

        // Append Since
        $query = $folder->query()->leaveUnread()->fetchOrderAsc();
        if(!empty($since)) {
            $this->log->info('Getting Messages From IMAP Since: "' . $since->toDateTimeString() . '"');
            $query = $query->since($since);
        }

        // Return Messages in Time Frame
        $messages = $query->get();
        if($messages->count() > 0) {
            $this->log->info('Found ' . $messages->count() . ' Messages to Process');
        }
        return $messages;
    }

    /**
     * Get Mailboxes From IMAP Config
     * 
     * @param Client $imap
     * @return Collection<ImapMailbox>
     */
    private function getMailboxes(Client $imap): Collection {
        // Get Mailboxes
        $folders = $imap->getFolders(false);

        // Create Imap Mailboxes
        $mailboxes = new Collection();
        foreach($folders as $folder) {
            $mailboxes->push(new ImapMailbox([
                'full' => $folder->path,
                'delimiter' => $folder->delimiter,
                'name' => $folder->name
            ]));
        }

        // Return Mailboxes
        $this->log->info("Found " . $mailboxes->count() . " mailboxes from IMAP");
        return $mailboxes;
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
