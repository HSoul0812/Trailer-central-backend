<?php

namespace App\Services\Integration\Auth;

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
     * Get All Messages in Specific Folder
     * 
     * @param array $params
     * @param string $folder folder name to get messages from; defaults to inbox
     * @return whether the email was sent successfully or not
     */
    public function getFolder($params, $folder = 'INBOX');
}