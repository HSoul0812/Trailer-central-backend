<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Email\ImapConnectionFailedException;
use PhpImap\Mailbox;
use Illuminate\Support\Facades\Log;
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
     * @return false || array of EmailHistory
     */
    public function messages($salesperson, $folder) {
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
            throw new ImapConnectionFailedException();
        }

        // Return Mailbox
        try {
            // Get Messages
            $emails = array();
            $replies = $this->getMessages($dateImported);
            if($replies !== false && count($replies) > 0) {
                // Parse Replies
                foreach($replies as $reply) {
                    // Parse Reply
                    $parsed = $this->parseReply($reply);
                    if($parsed !== false) {
                        // Append Emails
                        $emails[] = $parsed;
                    }
                }
            }
        } catch (ConnectionException $e) {
            // Logged Exceptions
            $error = $e->getMessage() . ': ' . $e->getTraceAsString();
            Log::error('Cannot connect to IMAP, exception returned: ' . $error);
        } catch (\Exception $e) {
            // Logged Exceptions
            $error = $e->getMessage() . ': ' . $e->getTraceAsString();
            Log::error('An unknown IMAP error occurred, exception returned: ' . $error);
        }
        $this->imap = null;

        // Return Array of Parsed Emails
        return $emails;
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
            Log::info('Getting Mail Info From IMAP With ID\'s: "' . implode(", ", $mailIds) . '"');
            return $this->imap->getMailsInfo($mailIds);
        }

        // No Mail ID's Found? Return Empty Array!
        return array();
    }

    /**
     * Parse Reply Details to Clean Up Result
     * 
     * @param type $overview
     * @return array of parsed data
     */
    private function parseReply($overview) {
        // Get Mail
        if(empty($overview->uid)) {
            return false;
        }

        // Get Mail Data
        $mail = $this->imap->getMail($overview->uid, false);
        if(empty($mail->subject)) {
            return false;
        }

        // Parse Message ID's
        $messageId = '';
        if(!empty($overview->in_reply_to)) {
            $messageId = $overview->in_reply_to;
        }
        if(!empty($overview->message_id)) {
            $messageId = $overview->message_id;
        }
        if(empty($messageId) && !empty($mail->messageId)) {
            $messageId = $mail->messageId;
        }

        // Handle Initializing Parsed Data
        $parsed = [
            'references' => !empty($overview->references) ? $overview->references : array(),
            'message_id' => $messageId,
            'root_id' => $messageId
        ];
        if(!empty($parsed['references'])) {
            $parsed['references'] = explode(" ", $parsed['references']);
            $parsed['root_id'] = reset($parsed['references']);
            if(empty($parsed['message_id'])) {
                $parsed['message_id'] = end($parsed['references']);
            }
        }

        // No Message ID?
        if(empty($parsed['message_id'])) {
            return false;
        }

        // Parse To Email/Name
        $toFull = !empty($overview->to) ? $overview->to : '';
        $to = explode("<", $toFull);
        $parsed['to_name'] = trim($to[0]);
        $parsed['to'] = '';
        if(!empty($to[1])) {
            $parsed['to'] = trim(str_replace(">", "", $to[1]));
        }
        if(empty($parsed['to'])) {
            $parsed['to'] = $parsed['to_name'];
            $parsed['to_name'] = '';
        }

        // Parse From Email/Name
        $fromFull = !empty($overview->from) ? $overview->from : '';
        $from = explode("<", $fromFull);
        $parsed['from_name'] = trim($from[0]);
        $parsed['from'] = '';
        if(!empty($from[1])) {
            $parsed['from'] = trim(str_replace(">", "", $from[1]));
        }
        if(empty($parsed['from'])) {
            $parsed['from'] = $parsed['from_name'];
            $parsed['from_name'] = '';
        }

        // Handle Subject
        $parsed['subject'] = !empty($mail->subject) ? $mail->subject : '';
        if(empty($parsed['subject'])) {
            $parsed['subject'] = !empty($overview->subject) ? $overview->subject : "";
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
            $files[] = [
                'tmpName' => $attachment->__get(),
                'filePath' => $attachment->name,
                'name' => $attachment->name,
                'data' => $attachment->getContents()
            ];
        }

        // Set Date
        $parsed['date'] = date("Y-m-d H:i:s", strtotime($overview->date));

        // Return Parsed Array
        unset($overview);
        unset($mail);
        return $parsed;
    }
}
