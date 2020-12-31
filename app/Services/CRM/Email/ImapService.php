<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\ImapConnectionFailedException;
use App\Exceptions\CRM\Email\ImapFolderConnectionFailedException;
use App\Exceptions\CRM\Email\ImapFolderUnknownErrorException;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\User\EmailFolder;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Support\Facades\Log;
use PhpImap\Mailbox;
use PhpImap\Exceptions\ConnectionException;

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
     * ScrapeRepliesService constructor.
     */
    public function __construct()
    {
        $this->attachmentDir = $_ENV['MAIL_ATTACHMENT_DIR'];
        if(!file_exists($this->attachmentDir)) {
            mkdir($this->attachmentDir);
        }
    }

    /**
     * Import Email Replies
     * 
     * @param SalesPerson $salesperson
     * @param EmailFolder $folder
     * @throws App\Exceptions\CRM\Email\ImapConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderConnectionFailedException
     * @throws App\Exceptions\CRM\Email\ImapFolderUnknownErrorException
     * @return array of emails
     */
    public function messages(SalesPerson $salesperson, EmailFolder $folder) {
        // Get IMAP
        $imported = (!empty($folder->date_imported) ? strtotime($folder->date_imported) : Carbon::now()->sub(1, 'month'));
        $imap = $this->connectIMAP($folder->name, [
            'email'    => !empty($salesperson->imap_email) ? $salesperson->imap_email : $salesperson->email,
            'password' => $salesperson->imap_password,
            'host'     => $salesperson->imap_server,
            'port'     => $salesperson->imap_port,
            'security' => (!empty($salesperson->imap_security) ? $salesperson->imap_security : 'ssl'),
            'charset'  => ($salesperson->smtp_auth === 'NTLM') ? EmailHistory::CHARSET_NTLM : EmailHistory::CHARSET_DEFAULT
        ], $imported);

        // Error Occurred
        if($imap === null) {
            throw new ImapConnectionFailedException;
        }

        // Return Mailbox
        try {
            // Get Messages
            return $this->getMessages($imported);
        } catch (ConnectionException $e) {
            throw new ImapFolderConnectionFailedException($e->getMessage());
        } catch (\Exception $e) {
            throw new ImapFolderUnknownErrorException;
        }
    }

    /**
     * Get Basic Overview
     * 
     * @param int $mailId
     * @return array of parsed data
     */
    public function overview(int $mailId) {
        // Get Mail
        $overview = reset($this->imap->getMailsInfo([$mailId]));
        if(empty($overview->uid)) {
            return false;
        }

        // Initialize Parsed Email
        $parsed = new ParsedEmail();
        $parsed->setId($overview->uid);

        // Set Message ID's
        $parsed->setMessageId(!empty($overview->in_reply_to) ? trim($overview->in_reply_to) : trim($overview->message_id));
        $parsed->setRootMessageId($parsed->getMessageId());
        $parsed->setReferences($overview->references);

        // Handle Overriding Message ID From References
        $references = $parsed->getReferences();
        if(!empty($references)) {
            $parsed->setRootMessageId(reset($parsed['references']));

            // Message ID Doesn't Exist?
            if(empty($parsed->getMessageId())) {
                $parsed->setMessageId(end($parsed['references']));
            }
        }

        // Set To/From
        $parsed->setTo($overview->to);
        $parsed->setFrom($overview->from);

        // Handle Subject
        $parsed->setSubject($overview->subject);

        // Set Date
        $parsed['date_sent'] = date("Y-m-d H:i:s", strtotime($overview->date));

        // Return Parsed Array
        $parsed['direction'] = 'Received';
        return $parsed;
    }

    /**
     * Full Reply Details to Clean Up Result
     * 
     * @param array $parsed
     * @return array of parsed data
     */
    public function full(array $parsed) {
        // Get Mail Data
        $mail = $this->imap->getMail($parsed['uid'], false);

        // Handle Subject
        if(!empty($mail->subject)) {
            $parsed['subject'] = $mail->subject;
        }

        // Handle Body
        $parsed['body'] = $mail->textHtml;
        $parsed['use_html'] = 1;
        if(empty($parsed['body'])) {
            $parsed['use_html'] = 0;
            $parsed['body'] = !empty($mail->textPlain) ? $mail->textPlain : "";
        }

        // Handle Attachments
        $parsed['attachments'] = $this->parseAttachments($mail);
        if(count($parsed['attachments']) > 0) {
            Log::info('Found ' . count($parsed['attachments']) . ' total attachments on Message ' . $parsed['message_id']);
        }

        // Return Parsed Array
        return $parsed;
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
            Log::info("Connecting to IMAP host: " . $hostname . " with email: " . $username);
            $this->imap = new Mailbox($hostname, $username, $password, $this->attachmentDir, $charset);
            Log::info('Connected to IMAP for email address: ' . $username);
        } catch (\Exception $e) {
            // Logged Exceptions
            $this->imap = null;
            $error = $e->getMessage() . ': ' . $e->getTraceAsString();
            Log::error('Cannot connect to ' . $username . ' via IMAP, exception returned: ' . $error);

            // Check for Chartype Error
            if(strpos($error, "BADCHARSET") !== FALSE) {
                Log::error('Detected bad CHARSET, cannot import emails on ' . $username);
            }
        }

        // Return IMAP Details
        return $this->imap;
    }

    /**
     * Get Messages After Set Date
     * 
     * @param string $time
     * @param int $days
     * @return array of emails
     */
    private function getMessages($time = 'days', $days = 7) {
        // Base Timestamp on Number of Days
        if($time === 'days') {
            $m = date("m");
            $d = date("d") - $days;
            $y = date("Y");
            $time = mktime(0, 0, 0, $m, $d, $y);
        }

        // Don't Implement Since if Time is 0
        if(empty($time) || !is_numeric($time)) {
            // Get All
            $search = "ALL";
        } else {
            // Create Date Search Expression
            $date = date('j M Y', $time);
            $search = 'SINCE "' . $date . '"';
        }

        // Imap Inbox ALREADY Exists?
        Log::info('Getting Messages From IMAP With Filter: "' . $search . '"');
        $mailIds = $this->imap->searchMailbox($search);
        if(count($mailIds) > 0) {
            Log::info('Found ' . count($mailIds) . ' Message ID\'s to Process');
            return $mailIds;
        }

        // No Mail ID's Found? Return Empty Array!
        return [];
    }

    /**
     * Parse To/From Email and Name
     * 
     * @param \stdclass $overview
     * @return array
     */
    private function parseToFrom($overview) {
        // Parse To Email/Name
        $to = explode("<", (!empty($overview->to) ? $overview->to : ''));
        $parsed = [];
        $parsed['to_name'] = trim($to[0]);
        if(!empty($to[1])) {
            $parsed['to_email'] = trim(str_replace(">", "", $to[1]));
        }
        if(empty($parsed['to_email'])) {
            $parsed['to_email'] = $parsed['to_name'];
            $parsed['to_name'] = '';
        }

        // Parse From Email/Name
        $from = explode("<", (!empty($overview->from) ? $overview->from : ''));
        $parsed['from_name'] = trim($from[0]);
        if(!empty($from[1])) {
            $parsed['from_email'] = trim(str_replace(">", "", $from[1]));
        }
        if(empty($parsed['from_email'])) {
            $parsed['from_email'] = $parsed['from_name'];
            $parsed['from_name'] = '';
        }

        // Return Items
        return $parsed;
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
