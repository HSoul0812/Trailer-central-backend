<?php

namespace App\Services\Integration\Google;

use App\Exceptions\Integration\Google\MissingGapiAccessTokenException;
use App\Exceptions\Integration\Google\MissingGapiIdTokenException;
use App\Exceptions\Integration\Google\MissingGapiClientIdException;
use App\Exceptions\Integration\Google\InvalidGapiIdTokenException;
use App\Exceptions\Integration\Google\InvalidGmailAuthMessageException;
use App\Exceptions\Integration\Google\FailedConnectGapiClientException;
use App\Exceptions\Integration\Google\FailedInitializeGmailMessageException;
use App\Exceptions\Integration\Google\FailedSendGmailMessageException;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Traits\MailHelper;

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
     * Construct Google Client
     */
    public function __construct(
        InteractionEmailServiceInterface $interactionEmail,
        GoogleGmailInterface $gmail
    ) {
        // Set Interfaces
        $this->interactionEmail = $interactionEmail;
        $this->client = $client;
        $this->gmail = $gmail;

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

        // Create Message ID
        if(empty($params['message_id'])) {
            $messageId = sprintf('%s@%s', $this->generateId(), $this->serverHostname());
        } else {
            $messageId = str_replace('<', '', str_replace('>', '', $params['message_id']));
        }
        $params['message_id'] = $messageId;

        // Configure Client
        $this->client->setAccessToken([
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'expires_in' => $accessToken->expires_in,
            'created' => strtotime($accessToken->issued_at)
        ]);
        $this->client->setScopes($accessToken->scope);


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
            $this->error('Exception returned on sending gmail email; ' . $e->getMessage() . ': ' . $e->getTraceAsString());
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
}
