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
     * @return whether the email was sent successfully or not
     */
    public function messages(AccessToken $accessToken, string $folder = 'Inbox', array $filters = []): Collection {
        // Get Graph
        try {
            // Initialize Microsoft Graph
            $graph = new Graph();
            $graph->setAccessToken($accessToken->getAccessToken());

            // Get All Messages!
            $folderId = $this->getFolderId($accessToken, $folder);
            $emails = $this->getMessages($graph, new Collection(), $folderId, $filters);

            // Return Collection of ParsedEmail
            $this->log->info('Got ' . $emails->count() . ' email messages from graph folder ' . $folder);
            return $emails;
        } catch (\Exception $e) {
            // Log Error
            $this->log->error('Exception returned on getting office 365 messages; ' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Empty Collection of ParsedEmail
        return new Collection();
    }

    /**
     * Get and Parse Individual Message
     * 
     * @param string $mailId
     * @return parsed message details
     */
    public function message(string $mailId): ParsedEmail {
        
    }


    /**
     * Get Messages Page-By-Page
     * 
     * @param Graph $graph
     * @param Collection $emails
     * @param string $folderId
     * @param array $filters
     * @param array $params
     * @return Collection
     */
    private function getMessages(Graph $graph, Collection $emails, string $folderId,
                                    array $filters = [], array $params = []): Collection {
        // Get Query Params
        $queryParams = [
            '$top' => $params['$top'] ?? self::PER_PAGE,
            '$skip' => $params['$skip'] ?? 0,
            '$count' => true,
            '$orderby' => self::ORDER_BY
        ];
        if(!empty($filters)) {
            $queryParams['$filters'] = implode(' and ', $filters);
        }

        // Append query parameters to the '/me/mailFolders/{id}/messages' url
        $query = '/me/mailFolders/' . $folderId . '/messages?' . http_build_query($queryParams);

        // Get Messages From Microsoft Account
        $messages = $graph->createRequest('GET', $query)->setReturnType(Message::class)->execute();
        if(count($messages) > 0) {
            foreach($messages as $message) {
                $emails->push($this->getParsedEmail($message));
            }

            // Get Next Batch of Messages if More Pages Exist
            $params['$skip'] = isset($params['$skip']) ? $params['$skip'] += self::PER_PAGE : self::PER_PAGE;
            return $this->getMessages($graph, $emails, $folderId, $filters, $params);
        }

        // Return Collection of Emails
        return $emails;
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
                ->setReturnType(Model\MailFolder::class)
                ->execute();

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
