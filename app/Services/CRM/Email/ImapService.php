<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\ImapConnectionFailedException;
use App\Exceptions\CRM\Email\ImapFolderConnectionFailedException;
use App\Exceptions\CRM\Email\ImapFolderUnknownErrorException;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\User\EmailFolder;
use PhpImap\Mailbox;
use PhpImap\Exceptions\ConnectionException;
use Illuminate\Support\Facades\Log;

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
        // NTLM?
        $charset = 'UTF-8';
        if($salesperson->smtp_auth === 'NTLM') {
            $charset = 'US-ASCII';
        }

        // Get IMAP
        $email = !empty($salesperson->imap_email) ? $salesperson->imap_email : $salesperson->email;
        $oneMonthAgo = (time() - (60 * 60 * 24 * 30));
        $dateImported = (!empty($folder->date_imported) ? strtotime($folder->date_imported) : $oneMonthAgo);
        $imap = $this->connectIMAP($folder->name, [
            'email'    => $email,
            'password' => $salesperson->imap_password,
            'host'     => $salesperson->imap_server,
            'port'     => $salesperson->imap_port,
            'security' => (!empty($salesperson->imap_security) ? $salesperson->imap_security : 'ssl'),
            'charset'  => $charset
        ], $dateImported);

        // Error Occurred
        if($imap === null) {
            throw new ImapConnectionFailedException;
        }

        // Return Mailbox
        try {
            // Get Messages
            $emails = $this->getMessages($dateImported);
        } catch (ConnectionException $e) {
            throw new ImapFolderConnectionFailedException($e->getMessage());
        } catch (\Exception $e) {
            throw new ImapFolderUnknownErrorException;
        }

        // Return Array of Parsed Emails
        return !empty($emails) ? $emails : array();
    }

    /**
     * Get Basic Overview
     * 
     * @param int $mailId
     * @return array of parsed data
     */
    public function overview(int $mailId) {
        // Get Mail
        $overviews = $this->imap->getMailsInfo([$mailId]);
        $overview = reset($overviews);
        if(empty($overview->uid)) {
            return false;
        }

        // Parse Message ID's
        $messageId = '';
        if(!empty($overview->in_reply_to)) {
            $messageId = trim($overview->in_reply_to);
        }
        if(!empty($overview->message_id)) {
            $messageId = trim($overview->message_id);
        }
        Log::info('Processing Email Message ' . $messageId);

        // Handle Initializing Parsed Data
        $parsed = [
            'references' => !empty($overview->references) ? $overview->references : array(),
            'message_id' => $messageId,
            'root_message_id' => $messageId,
            'uid' => $overview->uid
        ];
        if(!empty($parsed['references'])) {
            $parsed['references'] = explode(" ", $parsed['references']);
            $parsed['root_message_id'] = trim(reset($parsed['references']));
            if(empty($parsed['message_id'])) {
                $parsed['message_id'] = trim(end($parsed['references']));
            }
        }

        // Parse To Email/Name
        $toFull = !empty($overview->to) ? $overview->to : '';
        $to = explode("<", $toFull);
        $parsed['to_name'] = trim($to[0]);
        $parsed['to_email'] = '';
        if(!empty($to[1])) {
            $parsed['to_email'] = trim(str_replace(">", "", $to[1]));
        }
        if(empty($parsed['to_email'])) {
            $parsed['to_email'] = $parsed['to_name'];
            $parsed['to_name'] = '';
        }

        // Parse From Email/Name
        $fromFull = !empty($overview->from) ? $overview->from : '';
        $from = explode("<", $fromFull);
        $parsed['from_name'] = trim($from[0]);
        $parsed['from_email'] = '';
        if(!empty($from[1])) {
            $parsed['from_email'] = trim(str_replace(">", "", $from[1]));
        }
        if(empty($parsed['from_email'])) {
            $parsed['from_email'] = $parsed['from_name'];
            $parsed['from_name'] = '';
        }

        // Handle Subject
        $parsed['subject'] = !empty($overview->subject) ? $overview->subject : "";

        // Set Date
        $parsed['date_sent'] = date("Y-m-d H:i:s", strtotime($overview->date));

        // Return Parsed Array
        return $parsed;
    }

    /**
     * Parse Reply Details to Clean Up Result
     * 
     * @param array $overview
     * @return array of parsed data
     */
    public function parsed(array $overview) {
        // Get Mail Data
        $mail = $this->imap->getMail($overview['uid'], false);
        $parsed = $overview;
        if(empty($overview['messageId']) && !empty($mail->messageId)) {
            $parsed['messageId'] = trim($mail->messageId);
        }

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
        $attachments = $mail->getAttachments();
        $files = array();
        foreach($attachments as $attachment) {
            $file = new \stdclass;
            $file->tmpName = $attachment->__get('filePath');
            $file->filePath = $attachment->name;
            $file->name = $attachment->name;
            $files[] = $file;
        }
        $parsed['attachments'] = $files;
        if(count($files) > 0) {
            Log::info('Found ' . count($files) . ' total attachments on Message ' . $overview['message_id']);
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

            // Authentication Error?!
            if(strpos($error, 'Can not authenticate to IMAP server') !== FALSE) {
                // Mark Connection as Failed!
                //$this->updateFolder($folder, false, false);
            }

            // Check for Chartype Error
            if(strpos($error, "BADCHARSET") !== FALSE) {
                preg_match('/\[BADCHARSET \((.*?)\)\]/', $error, $matches);
                if(isset($matches[1]) && !empty($matches[1])) {
                    Log::error('Detected bad CHARSET! Trying to load again with ' . $charset);
                }
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
        return array();
    }
}
