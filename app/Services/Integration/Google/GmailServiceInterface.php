<?php

namespace App\Services\Integration\Google;

use App\Models\Integration\Auth\AccessToken;

interface GmailServiceInterface {
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
     * @param obj $item
     * @return parsed message details
     */
    public function message($item);

    /**
     * Get All Labels for User
     * 
     * @param AccessToken $accessToken
     * @param string $search
     * @throws App\Exceptions\Integration\Google\MissingGmailLabelsException
     * @throws App\Exceptions\Integration\Google\MissingGmailLabelException
     * @return array of labels
     */
    public function labels(AccessToken $accessToken, string $search = '');
}