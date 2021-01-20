<?php

namespace App\Services\Integration\Google;

use App\Exceptions\Integration\Google\MissingGapiAccessTokenException;
use App\Exceptions\Integration\Google\MissingGapiIdTokenException;
use App\Exceptions\Integration\Google\MissingGapiClientIdException;
use App\Exceptions\Integration\Google\InvalidGapiIdTokenException;
use App\Exceptions\Integration\Google\InvalidGmailAuthMessageException;
use App\Exceptions\Integration\Google\MissingGmailLabelsException;
use App\Exceptions\Integration\Google\MissingGmailLabelException;
use App\Exceptions\Integration\Google\FailedConnectGapiClientException;
use App\Exceptions\Integration\Google\FailedInitializeGmailMessageException;
use App\Exceptions\Integration\Google\FailedSendGmailMessageException;
use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Traits\MailHelper;
use Google_Service_Gmail_MessagePart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class GoogleService
 * 
 * @package App\Services\Integration\Google
 */
class GmailService implements GmailServiceInterface
{
    use MailHelper;

    /**
     * @var App\Services\CRM\Interactions\InteractionEmailServiceInterface
     */
    protected $interactionEmail;

    /**
     * @var Google_Client
     */
    protected $client;

    /**
     * @var Google_Service_Gmail_Message
     */
    protected $message;

    /**
     * @var array
     */
    protected $validQuery = [
        'after', 'before', 'older', 'newer'
    ];

    /**
     * Construct Google Client
     */
    public function __construct(InteractionEmailServiceInterface $interactionEmail) {
        // Set Interfaces
        $this->interactionEmail = $interactionEmail;

        // No Client ID?!
        if(empty($_ENV['GOOGLE_OAUTH_CLIENT_ID'])) {
            throw new MissingGapiClientIdException;
        }

        // Initialize Client
        $this->client = new \Google_Client();
        $this->client->setApplicationName($_ENV['GOOGLE_OAUTH_APP_NAME']);
        $this->client->setClientId($_ENV['GOOGLE_OAUTH_CLIENT_ID']);
        if(empty($this->client)) {
            throw new FailedConnectGapiClientException;
        }
        $this->client->setAccessType('offline');
    }

    /**
     * Send Email Email
     * 
     * @param AccessToken $accessToken
     * @throws App\Exceptions\Integration\Google\FailedSendGmailMessageException
     * @throws App\Exceptions\Integration\Google\FailedInitializeGmailMessageException
     * @throws App\Exceptions\Integration\Google\InvalidGmailAuthMessageException
     * @return array of validation info
     */
    public function send(AccessToken $accessToken, array $params) {
        // Set Access Token
        $this->setAccessToken($accessToken);

        // Create Message ID
        if(empty($params['message_id'])) {
            $messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());
        } else {
            $messageId = str_replace('<', '', str_replace('>', '', $params['message_id']));
        }
        $params['message_id'] = $messageId;


        // Insert Gmail
        try {
            // Create Message
            $message = $this->prepareMessage($params);
        } catch (\Exception $e) {
            throw new FailedInitializeGmailMessageException($e->getMessage() . ': ' . $e->getTraceAsString());
        }
        if(empty($message)) {
            throw new FailedInitializeGmailMessageException();
        }

        // Send Gmail Message
        try {
            // Send Message
            $sent = $this->gmail->users_messages->send('me', $message);
        } catch (\Exception $e) {
            // Get Message
            $error = $e->getMessage();
            Log::error('Exception returned on sending gmail email; ' . $e->getMessage() . ': ' . $e->getTraceAsString());
            if(strpos($error, "invalid authentication") !== FALSE) {
                throw new InvalidGmailAuthMessageException();
            } else {
                throw new FailedSendGmailMessageException();
            }
        }

        // Store Attachments
        if(isset($params['attachments'])) {
            $params['attachments'] = $this->interactionEmail->storeAttachments($params['attachments'], $accessToken->dealerId, $params['message_id']);
        }

        // Return Results
        return $params;
    }

    /**
     * Get All Messages With Label
     * 
     * @param AccessToken $accessToken
     * @param string $folder folder name to get messages from; defaults to inbox
     * @param array $params
     * @return whether the email was sent successfully or not
     */
    public function messages(AccessToken $accessToken, string $folder = 'INBOX', array $params = []) {
        // Configure Client
        $this->setAccessToken($accessToken);

        // Get Labels
        $labels = $this->labels($accessToken, [$folder]);

        // Imap Inbox ALREADY Exists?
        $q = '';
        foreach($params as $k => $v) {
            if(in_array($k, $this->validQuery)) {
                $q .= $k . ': ' . $v;
            }
        }
        Log::info('Getting Messages From Gmail Label ' . $folder . ' with filters: "' . $q . '"');

        // Get Messages
        $results = $this->gmail->users_messages->listUsersMessages('me', [
            'labelIds' => $this->getLabelIds($labels),
            'q' => $q
        ]);
        $messages = $results->getMessages();

        // Return Results
        Log::info('Found ' . count($messages) . ' Messages to Process for Label ' . $folder);
        if (count($messages) == 0) {
            return [];
        }

        // Return Mapped Array
        return array_map(function($item) {
            return $item->id;
        }, $messages);
    }

    /**
     * Get and Parse Individual Message
     * 
     * @param string $mailId
     * @return parsed message details
     */
    public function message(string $mailId) {
        // Get Message
        $message = $this->gmail->users_messages->get('me', $mailId, ['format' => 'full']);

        // Get Headers
        $payload = $message->getPayload();
        if(empty($payload)) {
            return [];
        }

        // Get Headers/Body/Attachments
        $headers = $this->parseMessageHeaders($payload->getHeaders());
        $body = $this->parseMessageBody($headers['Message-ID'], $payload);
        $attachments = new Collection();
        if(!empty($payload->parts)) {
            $attachments = $this->parseMessageAttachments($headers['Message-ID'], $payload->parts);
        }
        if(count($attachments) > 0) {
            Log::info('Found ' . count($attachments) . ' total attachments on Message ' . $headers['Message-ID']);
        }

        // Parse Data
        return $this->getParsedMessage($mailId, $headers, $body, $attachments);
    }

    /**
     * Move Message Labels
     * 
     * @param string $mailId mail ID to modify
     * @param array $labels labels to add by name | required
     * @param array $remove labels to remove by name | optional
     * @return true on success, false on failure
     */
    public function move(AccessToken $accessToken, string $mailId, array $labels, array $remove = []): bool {
        // Create Modify Message Request
        $newLabels = $this->labels($accessToken, $labels);
        $modify = new Google_Service_Gmail_ModifyMessageRequest();
        $modify->setAddLabelIds($this->getLabelIds($newLabels));

        // Remove Labels Exist?
        if(!empty($remove)) {
            $removedLabels = $this->labels($accessToken, $remove);
            $modify->setRemoveLabelIds($this->getLabelIds($removedLabels));
        }

        // Move Message
        $result = $this->gmail->users_messages->modify('me', $mailId, $modify);

        // Success?
        return !empty($result);
    }

    /**
     * Get All Labels for User
     * 
     * @param AccessToken $accessToken
     * @param string $search
     * @param bool $single
     * @throws App\Exceptions\Integration\Google\MissingGmailLabelsException
     * @throws App\Exceptions\Integration\Google\MissingGmailLabelException
     * @return array of labels
     */
    public function labels(AccessToken $accessToken, array $search = []) {
        // Configure Client
        $this->setAccessToken($accessToken);

        // Get Labels
        $results = $this->gmail->users_labels->listUsersLabels('me');
        if(count($results->getLabels()) == 0) {
            throw new MissingGmailLabelsException;
        }

        // Get Labels
        $labels = [];
        foreach($results->getLabels() as $label) {
            // Search for Label Exists?
            if(!empty($search)) {
                // Skip If Label Doesn't Match!
                if(in_array($label->getName(), $search)) {
                    continue;
                }
            }

            // Add Label to Array
            $labels[] = $label;
        }

        // None Exist?!
        if(count($labels) < 1) {
            throw new MissingGmailLabelException;
        }

        // Return Labels
        return $labels;
    }


    /**
     * Set Access Token on Client
     * 
     * @param type $accessToken
     * @return void
     */
    private function setAccessToken(AccessToken $accessToken) {
        // ID Token Exists?
        if(empty($accessToken->id_token)) {
            throw new MissingGapiIdTokenException;
        }

        // Set Access Token on Client
        $this->client->setAccessToken([
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'expires_in' => $accessToken->expires_in,
            'created' => strtotime($accessToken->issued_at) * 1000
        ]);
        $this->client->setScopes($accessToken->scope);

        // Setup Gmail
        $this->gmail = new \Google_Service_Gmail($this->client);
    }

    /**
     * Prepare Message to Send to Gmail
     * 
     * @param array $params
     * @return Google_Service_Gmail_Message
     */
    private function prepareMessage($params) {
        // Get From
        $from = $params['from_email'];
        if(isset($params['from_name'])) {
            $from = [$params['from_email'] => $params['from_name']];
        }

        // Create Swift Message
        $message = (new \Swift_Message($params['subject']))
            ->setFrom($from)
            ->setTo([$params['to_email'] => $params['to_name']])
            ->setContentType('text/html')
            ->setCharset('utf-8')
            ->setBody($params['body']);

        // Set Message ID
        $message->getHeaders()->get('Message-ID')->setId($params['message_id']);

        // Add Existing Attachments
        if(isset($params['files'])) {
            $files = $this->interactionEmail->cleanAttachments($params['files']);
            foreach($files as $attachment) {
                // Optionally add any attachments
                $message->attach((new \Swift_Attachment(file_get_contents($attachment['path']), $attachment['as'], $attachment['mime'])));
            }
        }

        // Add Attachments
        if(isset($params['attachments'])) {
            $attachments = $this->interactionEmail->getAttachments($params['attachments']);
            foreach($attachments as $attachment) {
                // Optionally add any attachments
                $message->attach(\Swift_Attachment::fromPath($attachment['path'])->setFilename($attachment['as']));
            }
        }

        // Get Raw Message
        $msg_base64 = (new \Swift_Mime_ContentEncoder_Base64ContentEncoder())
                        ->encodeString($message->toString());
        $msg_base64 = preg_replace('/(\s|\r)*/', '', $msg_base64);

        // Set Message and Return
        $this->message = new \Google_Service_Gmail_Message();
        $this->message->setRaw($msg_base64);

        // Return Message
        return $this->message;
    }


    /**
     * Get All Label ID's for User
     * 
     * @param array labels
     * @return array of label ID's
     */
    private function getLabelIds(array $labels) {
        // Initialize Label ID's
        $labelIds = [];
        foreach($labels as $label) {
            $labelIds[] = $label['id'];
        }

        // Return Array
        return $labelIds;
    }

    /**
     * Parse Message Headers Into More Reasonable Format
     * 
     * @param array $headers
     * @return array
     */
    private function parseMessageHeaders(array $headers) {
        // Initialize New Headers Array
        $clean = [];
        foreach($headers as $header) {
            // Clean Name
            if($header->name === 'Message-Id') {
                $header->name = 'Message-ID';
            } elseif($header->name === 'Delivered-To') {
                $header->name = 'To';
            }

            // Add to Array
            $clean[$header->name] = trim($header->value);
        }
        return $clean;
    }

    /**
     * Parse Message Into Body
     * 
     * @param string $message_id
     * @param Google_Service_Gmail_MessagePart|array $payload
     * @source https://stackoverflow.com/a/32660892
     * @return string of body
     */
    private function parseMessageBody(string $message_id, $payload) {
        // Get Body From Parts
        $body = '';
        if(is_array($payload)) {
            foreach ($payload as $part) {
                if (!empty($part->body->data)) {
                    $body = $part->body->data;
                    break;
                } elseif (!empty($part->parts)) {
                    $body = $this->parseMessageBody($message_id, $part->parts);
                }
            }
            return $body;
        }

        // Handle Normal Payload Data
        if (!empty($payload->body->data)) {
            $body = $payload->body->data;
        } else if (!empty($payload->parts)) {
            $body = $this->parseMessageBody($message_id, $payload->parts);
        }

        // Clean Result Body
        $decoded = str_replace(['-', '_'], ['+', '/'], $body);
        $cleaned = base64_decode($decoded);

        // Return Result
        return $cleaned;
    }

    /**
     * Parse Message Into Attachments
     * 
     * @param string $message_id
     * @param array $parts
     * @source https://stackoverflow.com/a/59400043
     * @return array of attachments
     */
    private function parseMessageAttachments(string $message_id, array $parts) {
        // Get Attachments From Parts
        $attachments = new Collection();
        foreach ($parts as $part) {
            if (!empty($part->body->attachmentId)) {
                $attachment = $this->gmail->users_messages_attachments->get('me', $message_id, $part->body->attachmentId);

                // Initialize File Class
                $file = new AttachmentFile();
                $file->setFilePath($part->filename);
                $file->setFileName($part->filename);
                $file->setMimeType($part->mimeType);
                $file->setContents(strtr($attachment->data, '-_', '+/'));

                // Add Attachment to Array
                $attachments->push($file);
            } else if (!empty($part->parts)) {
                $attachments = $attachments->concat($this->parseMessageAttachments($message_id, $part->parts));
            }
        }

        // Collect Attachments
        return $attachments;
    }

    /**
     * Get Parsed Message
     * 
     * @param string $mailId
     * @param array $headers
     * @param string $body
     * @param Collection<AttachmentFile> $attachments
     * @return ParsedEmail
     */
    private function getParsedMessage(string $mailId, array $headers, string $body, Collection $attachments) {
        // Create Parsed Email
        $parsed = new ParsedEmail();
        $parsed->setId($mailId);

        // Set Message ID
        $parsed->setMessageId($headers['Message-ID']);

        // Set To/From
        $parsed->setTo($headers['To']);
        $parsed->setFrom($headers['From']);

        // Set Subject/Body
        $parsed->setSubject($headers['Subject']);
        $parsed->setBody($body);
        $parsed->setAttachments($attachments);

        // Set Date
        $parsed->setDate($headers['Date']);

        // Return ParsedEmail
        return $parsed;
    }
}
