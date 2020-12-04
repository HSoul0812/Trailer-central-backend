<?php

namespace App\Services\Integration\Google;

interface GmailServiceInterface {
    /**
     * Validate Google API Access Token Exists
     * 
     * @param AccessToken $accessToken
     * @param array $params
     * @return message ID of successfully sent email
     */
    public function send($accessToken, $params);

    /**
     * Get All Messages With Label
     * 
     * @param AccessToken $accessToken
     * @param string $folder folder name to get messages from; defaults to inbox
     * @param array $params
     * @return whether the email was sent successfully or not
     */
    public function messages($accessToken, $folder = 'INBOX', $params = []);

    /**
     * Get All Labels for User
     * 
     * @param AccessToken $accessToken
     * @param string || null $search
     * @return array of labels || single label
     */
    public function labels($accessToken, $search = null);
}