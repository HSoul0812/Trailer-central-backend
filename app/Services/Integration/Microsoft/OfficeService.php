<?php

namespace App\Services\Integration\Microsoft;

use App\Exceptions\Integration\Microsoft\MissingAzureIdTokenException;
use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Utilities\Fractal\NoDataArraySerializer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Attachment;
use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\MailFolder;
use Microsoft\Graph\Model\Message;

/**
 * Class OfficeService
 *
 * @package App\Services\Integration\Microsoft
 */
class OfficeService extends AzureService implements OfficeServiceInterface
{
    /**
     * @const Get Office Scope Prefix
     */
    const SCOPE_OFFICE = 'https://outlook.office.com/';

    /**
     * @const Default Folder Name
     */
    const DEFAULT_FOLDER = 'Inbox';

    /**
     * @const Emails Per Page
     */
    const PER_PAGE = 10;

    /**
     * @const Emails Order By
     */
    const ORDER_BY = 'SentDateTime';


    /**
     * Create Microsoft Azure Log
     */
    public function __construct(Manager $fractal)
    {
        // Initialize Services
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());

        // Initialize Logger
        $this->log = Log::channel('azure');
    }


    /**
     * Validate Microsoft Azure Access Token Exists and Refresh if Possible
     *
     * @param AccessToken $accessToken
     * @throws MissingAzureIdTokenException
     * @return ValidateToken
     */
    public function validate(AccessToken $accessToken): ValidateToken {
        // ID Token Exists?
        if(empty($accessToken->id_token)) {
            throw new MissingAzureIdTokenException;
        }

        // Initialize Email Token
        $emailToken = new EmailToken();
        $emailToken->fillFromToken($accessToken);

        // Validate By Custom Now
        return $this->validateCustom($emailToken);
    }

    /**
     * Validate Microsoft Azure Access Token Exists and Refresh if Possible
     *
     * @param CommonToken $accessToken
     * @return ValidateToken
     */
    public function validateCustom(CommonToken $accessToken): ValidateToken {
        // Configure Client
        $profile = $this->profile($accessToken);

        // Valid/Expired
        $isValid = ($profile !== null ? true : false);
        $isExpired = ($profile !== null ? $profile->isExpired() : true);

        // Try to Refresh Access Token!
        if($accessToken->refreshToken && (!$isValid || $isExpired)) {
            $refresh = $this->refreshCustom($accessToken);
            if($refresh->exists()) {
                $newProfile = $this->profile($refresh);
                $isValid = ($newProfile !== null ? true : false);
                $isExpired = false;
            }
        }
        if(empty($isValid)) {
            $isExpired = true;
        }

        // Return Payload Results
        return new ValidateToken([
            'new_token' => $refresh ?? null,
            'is_valid' => $isValid,
            'is_expired' => $isExpired,
            'message' => $this->getValidateMessage($isValid, $isExpired)
        ]);
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
        
    }

    /**
     * Get All Messages With Label
     * 
     * @param AccessToken $accessToken
     * @param string $folder folder name to get messages from; defaults to inbox
     * @param array<string> $filters
     * @return Collection<Message>
     */
    public function messages(AccessToken $accessToken, string $folder = 'Inbox',
                                array $filters = []): Collection {
        // Get Graph
        try {
            // Initialize Microsoft Graph
            $graph = new Graph();
            $graph->setAccessToken($accessToken->access_token);

            // Get All Messages!
            $folderId = $this->getFolderId($accessToken->access_token, $folder);
            $emails = $this->getMessages($graph, new Collection(), $folderId, $filters);

            // Return Collection of ParsedEmail
            $this->log->info('Got ' . $emails->count() . ' email messages from graph folder ' . $folder);
            return $emails;
        } catch (\Exception $e) {
            // Log Error
            $this->log->error('Exception returned on getting office 365 messages; ' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Empty Collection of Message
        return new Collection();
    }

    /**
     * Get and Parse Individual Message
     * 
     * @param Message $message
     * @return ParsedEmail
     */
    public function message(Message $message): ParsedEmail {
        // Get Headers/Body/Attachments
        $body = $message->getBody();

        // Get From/To
        $from = $message->getFrom();
        $fromEmail = !empty($from) ? $from->getEmailAddress() : null;
        $to = $message->getTo();
        $toEmail = !empty($to) ? $to->getEmailAddress() : null;

        // Parse Data
        return new ParsedEmail([
            'id' => $message->getId(),
            'message_id' => $message->getInternetMessageId(),
            'root_message_id' => $message->getInternetMessageId(),
            'from_email' => !empty($fromEmail) ? $fromEmail->getAddress() : '',
            'from_name' => !empty($fromEmail) ? $fromEmail->getName() : '',
            'to_email' => !empty($toEmail) ? $toEmail->getAddress() : '',
            'to_name' => !empty($toEmail) ? $toEmail->getName() : '',
            'subject' => $body->getSubject(),
            'body' => $body->getContent(),
            'is_html' => ($body->getContentType() === BodyType::HTML),
            'date' => $body->getSentDateTime(),
            'has_attachments' => $message->getHasAttachments()
        ]);
    }

    /**
     * Parse Full Message Details
     * 
     * @param AccessToken $accessToken
     * @param ParsedEmail $email
     * @return ParsedEmail
     */
    public function full(AccessToken $accessToken, ParsedEmail $email): ParsedEmail {
        // Get Attachments for Message
        $attachments = new Collection();
        if($email->hasAttachments) {
            $attachments = $this->getAttachments($accessToken->access_token, $email->id);
            if($attachments->count() > 0) {
                $this->log->info('Found ' . count($attachments) . ' total attachments on Message ' . $headers->messageId);
            }
        }
        $email->setAttachments($attachments);

        // Return Updated ParsedEmail
        return $email;
    }


    /**
     * Get Messages Page-By-Page
     * 
     * @param Graph $graph
     * @param Collection $emails
     * @param string $folderId
     * @param array $filters
     * @param array $params
     * @return Collection<Message>
     */
    private function getMessages(Graph $graph, Collection $emails, string $folderId,
                                    array $filters = [], array $params = []): Collection {
        // Get Query Params
        $queryParams = [
            '$top' => $params['$top'] ?? self::PER_PAGE,
            '$skip' => $params['$skip'] ?? 0,
            '$count' => 'true',
            '$orderby' => self::ORDER_BY
        ];
        if(!empty($filters)) {
            $queryParams['$filters'] = implode(' and ', $filters);
        }

        // Append query parameters to the '/me/mailFolders/{id}/messages' url
        $this->log->info('Get ' . $queryParams['$top'] . ' Messages from Folder ' . $folderId .
                            ' Starting From ' . $queryParams['$skip']);
        $query = '/me/mailFolders/' . $folderId . '/messages?' . http_build_query($queryParams);

        // Get Messages From Microsoft Account
        $messages = $graph->createRequest('GET', $query)->setReturnType(Message::class)->execute();
        $current = $emails->count();
        foreach($messages as $message) {
            $emails->push($message);
        }

        // New Emails Were Added?!
        if($current < $emails->count()) {
            // Get Next Batch of Messages if More Pages Exist
            $params['$skip'] = isset($params['$skip']) ? $params['$skip'] += self::PER_PAGE : self::PER_PAGE;
            return $this->getMessages($graph, $emails, $folderId, $filters, $params);
        }

        // Return Collection of Emails
        return $emails;
    }

    /**
     * Get Attachments
     * 
     * @param string $accessToken
     * @param string $emailId
     * @return Collection<AttachmentFile>
     */
    private function getAttachments(string $accessToken, string $emailId): Collection {
        // Initialize Microsoft Graph
        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        // Get Query for Message Attachments
        $query = '/me/messages/' . $emailId . '/attachments?$top=100';

        // Get Messages From Microsoft Account
        $attachments = new Collection();
        $files = $graph->createRequest('GET', $query)->setReturnType(Message::class)->execute();
        if(count($files) > 0) {
            foreach($files as $file) {
                $attachments->push(new AttachmentFile([
                    'file_path' => $file->getName(),
                    'file_name' => $file->getName(),
                    'mime_type' => $file->getContentType(),
                    'file_size' => $file->getSize(),
                    'contents'  => $file->getContentBytes()
                ]));
            }
        }

        // Return Collection of Emails
        return $attachments;
    }

    /**
     * Get Folder ID For Specific Folder Name
     * 
     * @param string $accessToken
     * @param string $name
     * @return string
     */
    private function getFolderId(string $accessToken, string $name): string {
        // Get Graph
        try {
            // Initialize Microsoft Graph
            $graph = new Graph();
            $graph->setAccessToken($accessToken);

            // Get Details From Microsoft Account
            $mailboxes = $graph->createRequest('GET', '/me/mailFolders?$filter=' .
                                                    "displayName eq '" . $name . "'")
                ->setReturnType(MailFolder::class)->execute();

            // Get Full Collection
            $folderId = '';
            foreach($mailboxes as $mailbox) {
                $folderId = $mailbox->getId();
            }
            $this->log->info('Got folder ID from graph: ' . $folderId . ' for folder name: ' . $name);
            return $folderId;
        } catch (\Exception $e) {
            // Log Error
            $this->log->error('Exception returned on getting office 365 folder ID; ' . $e->getMessage() . ': ' . $e->getTraceAsString());
            return self::DEFAULT_FOLDER;
        }
    }

    /**
     * Get Validation Message
     * 
     * @param bool $valid
     * @param bool $expired
     * @return string
     */
    private function getValidateMessage(bool $valid = false, bool $expired = false): string {
        // Return Validation Message
        if(!empty($valid)) {
            if(!empty($expired)) {
                return 'Your Office 365 Authorization has expired! Please try connecting again.';
            } else {
                return 'Your Office 365 Authorization has been validated successfully!';
            }
        }
        return 'Your Office 365 Authorization failed! Please try connecting again.';
    }
}
