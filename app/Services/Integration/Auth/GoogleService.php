<?php

namespace App\Services\Integration\Auth;

use App\Exceptions\Integration\Auth\MissingGapiAccessTokenException;
use App\Exceptions\Integration\Auth\MissingGapiIdTokenException;
use App\Exceptions\Integration\Auth\MissingGapiClientIdException;
use App\Exceptions\Integration\Auth\FailedConnectGapiClientException;

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
    public function __construct()
    {
        // No Client ID?!
        if(empty($_ENV['GOOGLE_OAUTH_CLIENT_ID'])) {
            throw new MissingGapiClientIdException;
        }

        // Initialize Client
        $this->client = new \Google_Client(['client_id' => $_ENV['GOOGLE_OAUTH_CLIENT_ID']]);
        if(empty($this->client)) {
            throw new FailedConnectGapiClientException;
        }
    }

    /**
     * Validate Google API Access Token Exists
     * 
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function validate($accessToken) {
        // ID Token Exists?
        if(empty($accessToken->id_token)) {
            throw new MissingGapiIdTokenException;
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
