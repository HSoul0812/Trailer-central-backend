<?php

namespace App\Services\Integration\Google;

use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ParsedEmail;

interface GmailServiceInterface {
    /**
     * Get Auth URL
     *
     * @param string $redirectUrl url to redirect auth back to again
     * @param string $authCode auth code to get full credentials with
     * @return array created from GoogleTokenTransformer
     */
    public function auth(string $redirectUrl, string $authCode): EmailToken;

    /**
     * Get Gmail Profile Email
     *
     * @param EmailToken $accessToken
     * @return null|EmailToken
     */
    public function profile(EmailToken $accessToken): ?EmailToken;

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
     * @return array whether the email was sent successfully or not
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
     * Move Message Labels
     *
     * @param AccessToken $accessToken
     * @param string $mailId mail ID to modify
     * @param array $labels labels to add by name | required
     * @param array $remove labels to remove by name | optional
     * @return true on success, false on failure
     */
    public function move(AccessToken $accessToken, string $mailId, array $labels, array $remove = []): bool;

    /**
     * Get All Labels for User
     *
     * @param AccessToken $accessToken
     * @param array $search
     * @throws App\Exceptions\Integration\Google\MissingGmailLabelsException
     * @throws App\Exceptions\Integration\Google\MissingGmailLabelException
     * @return array of labels
     */
    public function labels(AccessToken $accessToken, array $search = []);

    /**
     * Set Key for Google Service
     *
     * @param string $key
     * @return string
     */
    public function setKey(string $key = ''): string;
}
