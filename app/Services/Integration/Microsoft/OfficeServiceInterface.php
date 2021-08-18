<?php

namespace App\Services\Integration\Microsoft;

use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\ValidateToken;
use Microsoft\Graph\Model\Message;

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
     * Send Office 365 Email
     *
     * @param SmtpConfig $smtpConfig
     * @param ParsedEmail $parsedEmail
     * @return ParsedEmail
     */
    public function send(SmtpConfig $smtpConfig, ParsedEmail $parsedEmail): ParsedEmail;

    /**
     * Get All Messages With Label
     * 
     * @param AccessToken $accessToken
     * @param string $folder folder name to get messages from; defaults to inbox
     * @param array<string> $filters
     * @return Collection<Message>
     */
    public function messages(AccessToken $accessToken, string $folder = 'Inbox',
                                array $filters = []): Collection;

    /**
     * Get and Parse Individual Message
     * 
     * @param Message $message
     * @return ParsedEmail
     */
    public function message(Message $message): ParsedEmail;

    /**
     * Parse Full Message Details
     * 
     * @param AccessToken $accessToken
     * @param ParsedEmail $email
     * @return ParsedEmail
     */
    public function full(AccessToken $accessToken, ParsedEmail $email): ParsedEmail;
}