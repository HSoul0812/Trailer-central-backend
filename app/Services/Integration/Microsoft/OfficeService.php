<?php

namespace App\Services\Integration\Microsoft;

use App\Exceptions\Common\MissingFolderException;
use App\Exceptions\Common\InvalidEmailCredentialsException;
use App\Exceptions\Integration\Microsoft\CannotReceiveOffice365MessagesException;
use App\Exceptions\Integration\Microsoft\MissingAzureIdTokenException;
use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Utilities\Fractal\NoDataArraySerializer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\FileAttachment;
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
    const PER_PAGE = 100;

    /**
     * @const Max Emails
     */
    const MAX_EMAILS = 10000;

    /**
     * @const Emails Order By
     */
    const ORDER_BY = 'SentDateTime';


    /**
     * Create Microsoft Azure Log
     * 
     * @param InteractionEmailServiceInterface $interactionEmail
     * @param Manager $fractal
     */
    public function __construct(
        InteractionEmailServiceInterface $interactionEmail,
        Manager $fractal
    ) {
        // Set Interfaces
        $this->interactionEmail = $interactionEmail;

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
            return new ValidateToken([
                'is_valid' => false,
                'is_expired' => true,
                'message' => $this->getValidateMessage()
            ]);
        }

        // Initialize Email Token
        $emailToken = EmailToken::fillFromToken($accessToken);

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
     * Send Office 365 Email
     *
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @return ParsedEmail
     */
    public function send(SmtpConfig $smtpConfig, ParsedEmail $parsedEmail): ParsedEmail {
        // Initialize Microsoft Graph
        $graph = new Graph();
        $graph->setAccessToken($smtpConfig->accessToken->access_token);

        // Create Message
        $message = new Message();
        $message->setInternetMessageId($parsedEmail->getMessageId());
        $message->setSubject($parsedEmail->subject);
        $message->setBody(['content' => $parsedEmail->body, 'contentType' => $parsedEmail->getBodyType()]);
        $message->setToRecipients([
            ['emailAddress' => ['name' => $parsedEmail->toName, 'address' => $parsedEmail->to]]
        ]);
        $message->setFrom(['emailAddress' => [
            'name' => $parsedEmail->fromName, 'address' => $parsedEmail->from
        ]]);

        // Get Attachments
        $message->setHasAttachments($parsedEmail->hasAttachments);
        if($parsedEmail->hasAttachments) {
            $message->setAttachments($this->fillAttachments($parsedEmail->getAllAttachments()));
        }
        $this->log->info("Sending Email Message: " . print_r($message, true));

        // Get Messages From Microsoft Account
        $email = $graph->createRequest('POST', '/me/sendMail')->attachBody(['Message' => $message])->execute();

        // Store Attachments
        if(!empty($parsedEmail->getAttachments())) {
            $parsedEmail->setAttachments($this->interactionEmail->storeAttachments($smtpConfig->getAccessToken()->dealer_id, $parsedEmail));
        }

        // Return Email
        return $parsedEmail;
    }

    /**
     * Get All Messages With Label
     * 
     * @param AccessToken $accessToken
     * @param string $folder folder name to get messages from; defaults to inbox
     * @param array<string> $filters
     * @throws MissingFolderException
     * @throws InvalidEmailCredentialsException
     * @throws CannotReceiveOffice365MessagesException
     * @return Collection<Message>
     */
    public function messages(AccessToken $accessToken, string $folder = 'Inbox',
                                array $filters = []): Collection {
        // Get Folder
        $folderId = $this->getFolderId($accessToken->access_token, $folder);
        if(empty($folderId)) {
            throw new MissingFolderException;
        }

        // Get Messages From Graph
        try {
            $graph = new Graph();
            $graph->setAccessToken($accessToken->access_token);

            // Get All Messages!
            $emails = $this->getMessages($graph, new Collection(), $folderId, $filters);

            // Return Collection of ParsedEmail
            $this->log->info('Got ' . $emails->count() . ' email messages from graph folder ' . $folder);
            return $emails;
        } catch (\Exception $e) {
            // Log Error
            $this->log->error('Exception returned on getting office 365 messages; ' . $e->getMessage());
            if(strpos($e->getMessage(), 'Access token has expired or is not yet valid')) {
                throw new InvalidEmailCredentialsException;
            }
            throw new CannotReceiveOffice365MessagesException;
        }
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
        $to = $message->getToRecipients();
        $toEmail = !empty($to) ? $to[0]['emailAddress'] : null;

        // Get Date
        $date = $message->getSentDateTime();
        if(empty($date)) {
            $date = new \DateTime();
        }

        // Parse Data
        return new ParsedEmail([
            'id' => $message->getId(),
            'message_id' => $message->getInternetMessageId(),
            'root_message_id' => $message->getInternetMessageId(),
            'to' => !empty($toEmail) ? $toEmail['address'] : '',
            'to_name' => !empty($toEmail) ? $toEmail['name'] : '',
            'from' => !empty($fromEmail) ? $fromEmail->getAddress() : '',
            'from_name' => !empty($fromEmail) ? $fromEmail->getName() : '',
            'subject' => $message->getSubject(),
            'body' => $body->getContent(),
            'is_html' => ($body->getContentType() === BodyType::HTML),
            'date' => Carbon::instance($date)->setTimezone('UTC')->toDateTimeString(),
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
                $this->log->info('Found ' . count($attachments) . ' total attachments on Message ' . $email->messageId);
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
            $queryParams['$filter'] = implode(' and ', $filters);
        }

        // Append query parameters to the '/me/mailFolders/{id}/messages' url
        $this->log->info('Get ' . $queryParams['$top'] . ' Messages Starting From ' .
                            $queryParams['$skip'] . ' in Folder ' . $folderId);
        $query = '/me/mailFolders/' . $folderId . '/messages?' . http_build_query($queryParams);
        $this->log->info('Running query ' . $query . ' to get Messages From Office 365');

        // Get Messages From Microsoft Account
        $messages = $graph->createRequest('GET', $query)->setReturnType(Message::class)->execute();
        $current = $emails->count();
        foreach($messages as $message) {
            $emails->push($message);
        }

        // New Emails Were Added?!
        if($current < $emails->count() || $emails->count() > self::MAX_EMAILS) {
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
        $files = $graph->createRequest('GET', $query)->setReturnType(FileAttachment::class)->execute();
        if(count($files) > 0) {
            foreach($files as $file) {
                // Get Attachments
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
     * Get Array of Office 365 Attachments
     * 
     * @param Collection $attachments
     * @return array<array{contentType: string,
     *                     name: string,
     *                     size: int,
     *                     contents: string}>
     */
    private function fillAttachments(Collection $attachments): array {
        // Initialize Attachments
        $files = [];

        // Loop Existing Attachments
        foreach($attachments as $attachment) {
            $files[] = new FileAttachment([
                '@odata.type' => '#microsoft.graph.fileAttachment',
                'contentType' => $attachment->mimeType,
                'name' => $attachment->fileName,
                'size' => $attachment->fileSize,
                'contentBytes' => $attachment->getContentsEncoded()
            ]);
        }

        // Return Final Array
        return $files;
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
            $folderId = $mailboxes[0]->getId();
            $this->log->info('Got folder ID from graph: ' . $folderId . ' for folder name: ' . $name);
            return $folderId;
        } catch (\Exception $e) {
            // Log Error
            $this->log->error('Exception returned on getting office 365 folder ID; ' . $e->getMessage());
            if(strpos($e->getMessage(), 'Access token has expired or is not yet valid')) {
                throw new InvalidEmailCredentialsException;
            }
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
