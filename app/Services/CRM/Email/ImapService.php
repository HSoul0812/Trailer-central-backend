<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\ImapConnectionFailedException;
use App\Exceptions\CRM\Email\ImapFolderConnectionFailedException;
use App\Exceptions\CRM\Email\ImapFolderUnknownErrorException;
use App\Services\CRM\Email\DTOs\ImapConfig;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use Illuminate\Support\Facades\Log;
use PhpImap\Mailbox;
use PhpImap\Exceptions\ConnectionException;
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
     * @return array of emails
     */
    public function messages(ImapConfig $imapConfig) {
        // Get IMAP
        $imap = $this->connectIMAP($imapConfig->getFolderName(), [
            'email'    => $imapConfig->getUsername(),
            'password' => $imapConfig->getPassword(),
            'host'     => $imapConfig->getHost(),
            'port'     => $imapConfig->getPort(),
            'security' => $imapConfig->getSecurity(),
            'charset'  => $imapConfig->getCharset()
        ]);

        // Error Occurred
        if($imap === null) {
            throw new ImapConnectionFailedException;
        }

        // Return Mailbox
        try {
            // Get Messages
            return $this->getMessages($imapConfig->getStartDate());
        } catch (ConnectionException $e) {
            throw new ImapFolderConnectionFailedException($e->getMessage());
        } catch (\Exception $e) {
            throw new ImapFolderUnknownErrorException($e->getMessage());
        }
    }

    /**
     * Get Basic Overview
     *
     * @param string $mailId
     * @return array of parsed data
     */
    public function overview(string $mailId) {
        // Get Mail
        $mailInfo = $this->imap->getMailsInfo([(int) $mailId]);
        $overview = reset($mailInfo);
        if(empty($overview->uid)) {
            return false;
        }

        // Initialize Parsed Email
        $parsed = new ParsedEmail();
        $parsed->setId((string) $overview->uid);

        // Set Message ID's
        $parsed->setMessageId(!empty($overview->in_reply_to) ? trim($overview->in_reply_to) : (!empty($overview->message_id) ? trim($overview->message_id) : ''));
        $parsed->setRootMessageId($parsed->getMessageId());
        if(!empty($overview->references)) {
            $parsed->setReferences($overview->references);
        }

        // Handle Overriding Message ID From References
        $references = $parsed->getReferences();
        if(!empty($references)) {
            $parsed->setRootMessageId($parsed->getFirstReference());

            // Message ID Doesn't Exist?
            if(empty($parsed->getMessageId())) {
                $parsed->setMessageId($parsed->getLastReference());
            }
        }

        // Set To/From
        if(!empty($overview->to)) {
            $parsed->setTo($overview->to);
        }
        $parsed->setFrom($overview->from);

        // Handle Subject
        if(!empty($overview->subject)) {
            $parsed->setSubject($overview->subject);
        }

        // Set Date
        $parsed->setDate($overview->date);

        // Return Parsed Array
        return $parsed;
    }

    /**
     * Full Reply Details to Clean Up Result
     *
     * @param ParsedEmail $email
     * @return ParsedEmail updated with additional details
     */
    public function full(ParsedEmail $email) {
        // Get Mail Data
        $mail = $this->imap->getMail((int) $email->getId(), false);

        // Set To/From
        if(empty($email->getToEmail()) && !empty($mail->to)) {
            $email->setTo($mail->to);
        }

        // Handle Subject
        if(empty($email->getSubject()) && !empty($mail->subject)) {
            $email->setSubject($mail->subject);
        }

        // Handle Body
        $email->setBody($mail->textHtml);
        if(empty($email->getBody())) {
            $email->setIsHtml(false);
            $email->setBody($mail->textPlain);
        }

        // Handle Attachments
        $email->setAttachments($this->parseAttachments($mail));
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
    private function connectIMAP($folder, $config) {
        // Get SMTP Config
        $ssl = '/imap/' . $config['security'];
        $hostname = '{' . $config['host'] . ':' . $config['port'] . $ssl . '}' . $folder;
        $username = $config['email'];
        $password = $config['password'];
        $charset  = $config['charset'];

        // Return Mailbox
        try {
            // Imap Inbox ALREADY Exists?
            $this->log->info("Connecting to IMAP host: " . $hostname . " with email: " . $username);
            $this->imap = new Mailbox($hostname, $username, $password, $this->attachmentDir, $charset);
            $this->log->info('Connected to IMAP for email address: ' . $username);
        } catch (\Exception $e) {
            // Logged Exceptions
            $this->imap = null;
            $error = $e->getMessage() . ': ' . $e->getTraceAsString();
            $this->log->error('Cannot connect to ' . $username . ' via IMAP, exception returned: ' . $error);

            // Check for Chartype Error
            if(strpos($error, "BADCHARSET") !== FALSE) {
                $this->log->error('Detected bad CHARSET, cannot import emails on ' . $username);
            }
        }

        // Return IMAP Details
        return $this->imap;
    }

    /**
     * Get Messages After Set Date
     *
     * @param string $time days || all || DATETIME
     * @param int $days
     * @return array of emails
     */
    private function getMessages($time = 'days', $days = 7) {
        // Base Timestamp on Number of Days
        if($time === 'days') {
            $time = Carbon::now()->startOfDay()->subDays($days);
        } elseif(!empty($time) && $time !== 'all') {
            $time = Carbon::parse($time);
        }

        // Don't Implement Since if Time is 0
        if(empty($time) || $time === 'all') {
            // Get All
            $search = "ALL";
        } else {
            // Create Date Search Expression
            $search = 'SINCE "' . $time->isoFormat('D MMMM YYYY') . '"';
        }

        // Imap Inbox ALREADY Exists?
        $this->log->info('Getting Messages From IMAP With Filter: "' . $search . '"');
        $mailIds = $this->imap->searchMailbox($search);
        if(count($mailIds) > 0) {
            $this->log->info('Found ' . count($mailIds) . ' Message ID\'s to Process');
            return $mailIds;
        }

        // No Mail ID's Found? Return Empty Array!
        return [];
    }

    /**
     * Parse Attachments From
     *
     * @param Mail $mail
     * @return array of files
     */
    private function parseAttachments($mail) {
        // Get Attachments
        $files = [];
        $attachments = $mail->getAttachments();
        foreach($attachments as $attachment) {
            // Initialize File Class
            $file = new AttachmentFile();
            $file->setTmpName($attachment->__get('filePath'));
            $file->setFilePath($attachment->name);
            $file->setFileName($attachment->name);

            // Get Mime Type
            $mime = mime_content_type($file->getTmpName());
            $file->setMimeType($mime);

            // Add Files to Array
            $files[] = $file;
        }

        // Return Attachments
        return collect($files);
    }
}
