<?php

namespace App\Services\Integration\Microsoft;

use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\ValidateToken;

interface OfficeServiceInterface extends AzureServiceInterface {
    /**
     * Validate Office Access Token Exists and Refresh if Possible
     *
     * @param AccessToken $accessToken
     * @throws MissingAzureIdTokenException
     * @return ValidateToken
     */
    public function validate(AccessToken $accessToken): ValidateToken;

    /**
     * Validate Office Access Token Exists and Refresh if Possible
     * 
     * @param CommonToken $accessToken
     * @return ValidateToken
     */
    public function validateCustom(CommonToken $accessToken): ValidateToken;

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
    public function send(SmtpConfig $smtpConfig, ParsedEmail $parsedEmail): ParsedEmail;

    /**
     * Get All Messages With Label
     * 
     * @param AccessToken $accessToken
     * @param string $folder folder name to get messages from; defaults to inbox
     * @param array $params
     * @return whether the email was sent successfully or not
     */
    public function messages(AccessToken $accessToken, string $folder = 'INBOX', array $params = []);

    /**
     * Get and Parse Individual Message
     * 
     * @param string $mailId
     * @return parsed message details
     */
    public function message(string $mailId);

    /**
     * Get All Folders for User
     * 
     * @param AccessToken $accessToken
     * @param array $search
     * @throws App\Exceptions\Integration\Google\MissingGmailLabelsException
     * @throws App\Exceptions\Integration\Google\MissingGmailLabelException
     * @return array of labels
     */
    public function folders(CommonToken $accessToken, array $search = []);
}