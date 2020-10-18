<?php

namespace App\Services\Integration\Auth;

use App\Exceptions\Integration\Auth\MissingGapiAccessTokenException;
use App\Exceptions\Integration\Auth\MissingGapiIdTokenException;
use App\Exceptions\Integration\Auth\MissingGapiClientIdException;
use App\Exceptions\Integration\Auth\InvalidGapiIdTokenException;
use App\Exceptions\Integration\Auth\FailedConnectGapiClientException;
use App\Services\Integration\Auth\GmailServiceInterface;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;

/**
 * Class GoogleService
 * 
 * @package App\Services\Integration\Auth
 */
class GmailService implements GmailServiceInterface
{
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
     * @var string
     */
    protected $messageId;

    /**
     * Construct Google Client
     */
    public function __construct(InteractionEmailServiceInterface $interactionEmail)
    {
        // Set Interaction Email Service
        $this->interactionEmail = $interactionEmail;

        // No Client ID?!
        if(empty($_ENV['GOOGLE_OAUTH_CLIENT_ID'])) {
            throw new MissingGapiClientIdException;
        }

        // Initialize Client
        $this->client = new \Google_Client([
            'application_name' => $_ENV['GOOGLE_OAUTH_APP_NAME'],
            'client_id' => $_ENV['GOOGLE_OAUTH_CLIENT_ID']
        ]);
        if(empty($this->client)) {
            throw new FailedConnectGapiClientException;
        }

        // Setup Gmail
        $this->gmail = new \Google_Service_Gmail($this->client);
    }

    /**
     * Send Email Email
     * 
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function send($accessToken, $params) {
        // ID Token Exists?
        if(empty($accessToken->id_token)) {
            throw new MissingGapiIdTokenException;
        }

        // Configure Client
        $this->client->setAccessToken([
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'expires_in' => $accessToken->expires_in,
            'created' => strtotime($accessToken->issued_at) * 1000
        ]);
        $this->client->setScopes($accessToken->scope);


        // Insert Gmail
        try {
            // Create Message
            $message = $this->prepareMessage($params);
        } catch (Exception $e) {
            throw new FailedInitializeGmailMessage();
        }

        // Send Gmail Message
        try {
            // Message Exists?!
            if(!empty($message)) {
                // Send Message
                $sent = $this->gmail->users_messages->send('me', $message);
                $params['message_id'] = $this->messageId;
            } else {
                // No Message Exists So It Didn't Get Sent?!
                throw new MissingGmailMessage();
            }
        } catch (Exception $e) {
            throw new FailedSendGmailMessage();
        }

        // Store Attachments
        if(isset($params['attachments'])) {
            $params['attachments'] = $this->interactionEmail->storeAttachments($params['attachments'], $accessToken->dealerId, $this->messageId);
        }

        // Return Results
        return $params;
    }

    /**
     * Get All Messages in Specific Folder
     * 
     * @param array $params
     * @param string $folder folder name to get messages from; defaults to inbox
     * @return whether the email was sent successfully or not
     */
    public function getFolder($accessToken, $params, $inbox = 'INBOX') {
        // ID Token Exists?
        if(empty($accessToken->id_token)) {
            throw new MissingGapiIdTokenException;
        }

        // Configure Client
        $this->client->setAccessToken([
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'expires_in' => $accessToken->expires_in,
            'created' => strtotime($accessToken->issued_at) * 1000
        ]);
        $this->client->setScopes($accessToken->scope);
        return null;
    }


    /**
     * Prepare Message to Send to Gmail
     * 
     * 
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

        // Add Attachments
        if(isset($params['attachments'])) {
            $attachments = $this->interactionEmail->getAttachments($params['attachments']);
            foreach($attachments as $attachment) {
                // Optionally add any attachments
                $message->attach(Swift_Attachment::fromPath($attachment['path']));
            }
        }

        // Get Message ID
        $this->messageId = $message->getHeaders()->get('Message-ID');

        // Get Raw Message
        $msg_base64 = (new \Swift_Mime_ContentEncoder_Base64ContentEncoder())
            ->encodeString($message->toString());

        // Set Message and Return
        $this->message = new \Google_Service_Gmail_Message();
        $this->message->setRaw($msg_base64);

        // Return Message
        return $this->message;
    }
}
