<?php

namespace App\Services\Integration\Google;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\EmailToken;

interface GmailServiceInterface {
    /**
     * Get Gmail Profile Email
     * 
     * @param EmailToken $emailToken
     * @return EmailToken
     */
    public function profile(EmailToken $emailToken): EmailToken;

    /**
     * Validate Google API Access Token Exists
     * 
     * @param AccessToken $accessToken
     * @param array $params
     * @return message ID of successfully sent email
     */
    public function send(AccessToken $accessToken, array $params);

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
     * Move Message Labels
     * 
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
}