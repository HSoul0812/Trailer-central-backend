<?php

namespace App\Services\Integration\Auth;

/**
 * Class GoogleService
 * 
 * @package App\Services\Integration\Auth
 */
class GoogleService implements GoogleServiceInterface
{
    /**
     * @var Google_Client
     */
    protected $client;

    /**
     * Construct Google Client
     */
    public function _construct() {
        $this->client = new \Google_Client(['client_id' => $_ENV['GOOGLE_OAUTH_CLIENT_ID']]);
    }

    /**
     * Validate Google API Access Token Exists
     * 
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function validate($accessToken) {
        // Access Token Exists?
        if(empty($accessToken->access_token)) {
            throw new MissingGapiAccessTokenException;
        }

        // Initialize Vars
        $result = [
            'is_valid' => false
        ];

        // Validate ID Token
        $payload = $this->client->verifyIdToken($accessToken->id_token);
        if ($payload) {
            $result['is_valid'] = true;
        }

        // Return Payload Results
        return $result;
    }
}
