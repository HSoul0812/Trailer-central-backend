<?php

namespace App\Services\Integration\Google;

use App\Exceptions\Common\InvalidEmailCredentialsException;
use App\Exceptions\Common\MissingFolderException;
use App\Exceptions\Integration\Google\MissingGapiIdTokenException;
use App\Exceptions\Integration\Google\MissingGmailLabelsException;
use App\Exceptions\Integration\Google\InvalidGmailAuthMessageException;
use App\Exceptions\Integration\Google\InvalidGoogleAuthCodeException;
use App\Exceptions\Integration\Google\InvalidToEmailAddressException;
use App\Exceptions\Integration\Google\FailedInitializeGmailMessageException;
use App\Exceptions\Integration\Google\FailedSendGmailMessageException;
use App\Exceptions\Integration\Google\MissingGapiAccessTokenException;
use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Google\DTOs\GmailHeaders;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Traits\MailHelper;
use App\Utilities\Fractal\NoDataArraySerializer;
use Google_Service_Gmail;
use Google_Service_Gmail_MessagePart;
use League\Fractal\Manager;
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
     * @var App\Services\Integration\Google\GoogleServiceInterface
     */
    protected $google;

    /**
     * @var Google_Service_Gmail
     */
    protected $gmail;

    /**
     * @var array
     */
    protected $validQuery = [
        'after', 'before', 'older', 'newer'
    ];

    /**
     * Construct Google Client
     */
    public function __construct(
        InteractionEmailServiceInterface $interactionEmail,
        GoogleServiceInterface $google,
        Manager $fractal
    ) {
        // Set Interfaces
        $this->interactionEmail = $interactionEmail;
        $this->google = $google;

        // Initialize Services
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());

        // Initialize Logger
        $this->log = Log::channel('google');
    }


    /**
     * Get Auth URL
     *
     * @param string $authCode auth code to get full credentials with
     * @param null|string $redirectUrl url to redirect auth back to again
     * @return EmailToken
     */
    public function auth(string $authCode, ?string $redirectUrl = null): EmailToken {
        // Set Redirect URL
        $client = $this->google->getClient();
        $client->setRedirectUri($redirectUrl);
        $this->log->info('Set Redirect URI ' . $redirectUrl . ' to get Access Token Using Auth Code');

        // Return Auth URL for Login
        $authToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $this->log->info('Used Auth Code "' . $authCode . '" to get token: ' . print_r($authToken, true));
        if(empty($authToken['access_token'])) {
            throw new InvalidGoogleAuthCodeException;
        }

        // Return Formatted Auth Token
        $accessToken = EmailToken::fillFromArray($authToken);

        // Get Profile
        $emailToken = $this->profile($accessToken);

        // Return Email Token
        return $emailToken ?? $accessToken;
    }

    /**
     * Get Gmail Profile Email
     *
     * @param EmailToken $accessToken
     * @return null|EmailToken
     */
    public function profile(EmailToken $accessToken): ?EmailToken {
        // Get Profile Details
        $this->setEmailToken($accessToken);

        // Insert Gmail
        try {
            // Get Gmail Profile
            $profile = $this->gmail->users->getProfile('me');

            // Add Email Address From Profile
            $params = $accessToken->toArray();
            $params['email_address'] = $profile->getEmailAddress();
            $emailToken = new EmailToken($params);
        } catch (\Exception $e) {
            // Log Error
            $this->log->error('Exception returned on getting gmail profile email; ' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Google Token
        return $emailToken ?? null;
    }

    /**
     * Send Gmail Email
     *
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @throws App\Exceptions\Integration\Google\InvalidToEmailAddressException
     * @throws App\Exceptions\Integration\Google\FailedSendGmailMessageException
     * @throws App\Exceptions\Integration\Google\FailedInitializeGmailMessageException
     * @throws App\Exceptions\Integration\Google\InvalidGmailAuthMessageException
     * @return array of validation info
     */
    public function send(SmtpConfig $smtpConfig, ParsedEmail $parsedEmail): ParsedEmail {
        // Set Access Token
        $this->setAccessToken($smtpConfig->getAccessToken());

        // Prepare Gmail Message
        try {
            $message = $this->prepareMessage($parsedEmail);
        } catch (\Exception $e) {
            if(strpos($e->getMessage(), 'Address in mailbox given') !== FALSE) {
                throw new InvalidToEmailAddressException($e->getMessage());
            }
            throw new FailedInitializeGmailMessageException($e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Get Message ID From Gmail
        $result = $this->sendMessage($message);
        if(!empty($result) && !empty($result->getMessageId())) {
            $parsedEmail->setMessageId($result->getMessageId());
        }

        // Store Attachments
        if(!empty($parsedEmail->getAttachments())) {
            $parsedEmail->setAttachments($this->interactionEmail->storeAttachments($smtpConfig->getAccessToken()->dealer_id, $parsedEmail));
        }

        // Return Updated Parsed Email
        return $parsedEmail;
    }

    /**
     * Get All Messages With Label
     *
     * @param AccessToken $accessToken
     * @param string $folder folder name to get messages from; defaults to inbox
     * @param array $params
     * @return array whether the email was sent successfully or not
     */
    public function messages(AccessToken $accessToken, string $folder = 'INBOX', array $params = []) {
        // Get Labels
        $labels = $this->labels($accessToken, [$folder]);

        // Imap Inbox ALREADY Exists?
        $q = '';
        foreach($params as $k => $v) {
            if(in_array($k, $this->validQuery)) {
                $q .= $k . ': ' . $v;
            }
        }
        $this->log->info('Getting Messages From Gmail Label ' . $folder . ' with filters: "' . $q . '"');

        // Get Messages
        try {
            $results = $this->gmail->users_messages->listUsersMessages('me', [
                'labelIds' => $this->getLabelIds($labels),
                'q' => $q
            ]);
            $messages = $results->getMessages();
	    if(empty($messages)) {
		$messages = [];
	    }
        } catch (\Exception $e) {
            $this->log->error('Exception thrown trying to retrieve gmail messages: ' . $e->getMessage());
            throw new InvalidEmailCredentialsException;
        }

        // Return Results
        $this->log->info('Found ' . count($messages) . ' Messages to Process for Label ' . $folder);
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
        $headers = GmailHeaders::parse($payload->getHeaders());
        $body = $this->parseMessageBody($headers->messageId, $payload);
        $attachments = new Collection();
        if(!empty($payload->parts)) {
            $attachments = $this->parseMessageAttachments($headers->messageId, $payload->parts);
        }
        if(count($attachments) > 0) {
            $this->log->info('Found ' . count($attachments) . ' total attachments on Message ' . $headers->messageId);
        }

        // Parse Data
        return $this->getParsedMessage($mailId, $headers, $body, $attachments);
    }

    /**
     * Move Message Labels
     *
     * @param AccessToken $accessToken
     * @param string $mailId mail ID to modify
     * @param array $labels labels to add by name | required
     * @param array $remove labels to remove by name | optional
     * @return true on success, false on failure
     */
    public function move(AccessToken $accessToken, string $mailId, array $labels, array $remove = []): bool {
        // Create Modify Message Request
        $newLabels = $this->labels($accessToken, $labels);
        $modify = new \Google_Service_Gmail_ModifyMessageRequest();
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
     * @throws InvalidEmailCredentialsException
     * @throws MissingFolderException
     * @return array of labels
     */
    public function labels(AccessToken $accessToken, array $search = []) {
        // Configure Client
        $this->setAccessToken($accessToken);

        // Get Labels
        try {
            $results = $this->gmail->users_labels->listUsersLabels('me');
            $this->log->info('Found ' . count($results->getLabels()) . ' total labels on Gmail Email');
            if(count($results->getLabels()) == 0) {
                throw new MissingGmailLabelsException;
            }
        } catch (\Exception $e) {
            $this->log->error('Exception thrown trying to retrieve labels: ' . $e->getMessage());
            if(strpos($e->getMessage(), 'Request had invalid authentication credentials')) {
                throw new InvalidEmailCredentialsException;
            }
            throw new MissingGmailLabelsException;
        }

        // Get Labels
        $labels = [];
        foreach($results->getLabels() as $label) {
            // Search for Label Exists?
            if(!empty($search) && !in_array($label->getName(), $search)) {
                continue;
            }

            // Add Label to Array
            $labels[] = $label;
        }

        // None Exist?!
        $this->log->info('Returned ' . count($labels) . ' labels on Gmail Email');
        if(count($labels) < 1) {
            throw new MissingFolderException;
        }

        // Return Labels
        return $labels;
    }

    /**
     * Set Key for Google Service
     *
     * @param string $key
     * @return string
     */
    public function setKey(string $key = ''): string {
        return $this->google->setKey($key);
    }


    /**
     * Set Access Token on Client
     *
     * @param type $accessToken
     * @return void
     */
    public function setAccessToken(AccessToken $accessToken) {
        // Access Token Exists?
        if(empty($accessToken->access_token)) {
            throw new MissingGapiAccessTokenException;
        }

        // Set Access Token on Client
        $client = $this->google->getClient();
        $client->setAccessToken([
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'expires_in' => $accessToken->expires_in,
            'created' => strtotime($accessToken->issued_at) * 1000
        ]);
        $client->setScopes($accessToken->scope);

        // Setup Gmail
        $this->gmail = new Google_Service_Gmail($client);
        return $this->gmail;
    }

    /**
     * Set Google Token on Client
     *
     * @param EmailToken $emailToken
     * @return void
     */
    public function setEmailToken(EmailToken $emailToken) {
        // Access Token Exists?
        if(empty($emailToken->getAccessToken())) {
            throw new MissingGapiAccessTokenException;
        }

        // Set Google Token on Client
        $client = $this->google->getClient();
        $client->setAccessToken([
            'access_token' => $emailToken->getAccessToken(),
            'id_token' => $emailToken->getIdToken(),
            'expires_in' => $emailToken->getExpiresIn(),
            'created' => $emailToken->getIssuedUnix()
        ]);
        $client->setScopes($emailToken->getScope());

        // Setup Gmail
        $this->gmail = new Google_Service_Gmail($client);
        return $this->gmail;
    }


    /**
     * Prepare Message to Send to Gmail
     *
     * @param ParsedEmail $parsedEmail
     * @return Google_Service_Gmail_Message
     */
    private function prepareMessage(ParsedEmail $parsedEmail) {
        // Get From
        $from = $parsedEmail->getFromEmail();
        if(!empty($parsedEmail->getFromName())) {
            $from = [$from => $parsedEmail->getFromName()];
        }

        // Get To
        $to = $parsedEmail->getToEmail();
        if(!empty($parsedEmail->getToName())) {
            $to = [$to => $parsedEmail->getToName()];
        }

        // Create Swift Message
        $swift = (new \Swift_Message($parsedEmail->getSubject()))->setFrom($from)->setTo($to)
            ->setContentType('text/html')->setCharset('utf-8')->setBody($parsedEmail->getBody());

        // Add Existing Attachments
        foreach($parsedEmail->getExistingAttachments() as $attachment) {
            $swift->attach((new \Swift_Attachment($attachment->getContents(), $attachment->getFileName(), $attachment->getMimeType())));
        }

        // Add Attachments
        foreach($parsedEmail->getAttachments() as $attachment) {
            $swift->attach(\Swift_Attachment::fromPath($attachment->getTmpName())->setFilename($attachment->getFileName()));
        }

        // Set Message and Return
        $msg_base64 = (new \Swift_Mime_ContentEncoder_Base64ContentEncoder())->encodeString($swift->toString());
        $message = new \Google_Service_Gmail_Message();
        $message->setRaw(preg_replace('/(\s|\r)*/', '', $msg_base64));

        // Return Message
        return $message;
    }

    /**
     * Send Gmail Message
     *
     * @param \Google_Service_Gmail_Message $message
     * @return ParsedEmail
     */
    private function sendMessage(\Google_Service_Gmail_Message $message): ParsedEmail {
        // Send Message Via Gmail
        try {
            $sent = $this->gmail->users_messages->send('me', $message);
        } catch (\Exception $e) {
            // Get Message
            $error = $e->getMessage();
            $this->log->error('Exception returned on sending gmail email; ' . $e->getMessage() . ': ' . $e->getTraceAsString());
            if(strpos($error, "invalid authentication") !== FALSE) {
                throw new InvalidGmailAuthMessageException();
            } else {
                throw new FailedSendGmailMessageException();
            }
        }

        // Get Message ID From Gmail
        return $this->message($sent->id);
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
     * @param GmailHeaders $headers
     * @param string $body
     * @param Collection<AttachmentFile> $attachments
     * @return ParsedEmail
     */
    private function getParsedMessage(string $mailId, GmailHeaders $headers, string $body, Collection $attachments) {
        // Create Parsed Email
        $parsed = new ParsedEmail();
        $parsed->setId($mailId);

        // Set Message ID
        $parsed->setMessageId($headers->messageId);

        // Set To/From
        $parsed->setTo($headers->getFullTo());
        $parsed->setFrom($headers->getFullFrom());

        // Set Subject/Body
        $parsed->setSubject($headers->subject);
        $parsed->setBody($body);
        $parsed->setAttachments($attachments);

        // Set Date
        $parsed->setDate($headers->getDate());

        // Return ParsedEmail
        return $parsed;
    }
}
