<?php

namespace App\Services\Integration\Auth;

use App\Exceptions\Integration\Auth\MissingGapiAccessTokenException;
use App\Exceptions\Integration\Auth\MissingGapiIdTokenException;
use App\Exceptions\Integration\Auth\MissingGapiClientIdException;
use App\Exceptions\Integration\Auth\InvalidGapiIdTokenException;
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
        $this->client = new \Google_Client([
            'application_name' => $_ENV['GOOGLE_OAUTH_APP_NAME'],
            'client_id' => $_ENV['GOOGLE_OAUTH_CLIENT_ID']
        ]);
        if(empty($this->client)) {
            throw new FailedConnectGapiClientException;
        }
    }

    /**
     * Validate Google API Access Token Exists and Refresh if Possible
     * 
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function validate($accessToken) {
        // ID Token Exists?
        if(empty($accessToken->id_token)) {
            throw new MissingGapiIdTokenException;
        }

        // Configure Client
        $this->client->setAccessToken([
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'expires_in' => $accessToken->expires_in,
            'created' => strtotime($accessToken->issued_at)
        ]);
        $this->client->setScopes($accessToken->scope);

        // Initialize Vars
        $result = [
            'access_token' => $accessToken->access_token,
            'is_valid' => false,
            'is_expired' => true,
            'errors' => []
        ];

        // Validate ID Token
        try {
            // Verify ID Token is Valid
            $payload = $this->client->verifyIdToken($accessToken->id_token);
            if ($payload) {
                $result['is_valid'] = true;
            }
        }
        catch (\Exception $e) {
            // We actually just want to verify this is true or false
            // If it throws an exception, that means its false, the token isn't valid
            // This exception can be used for other processes but isn't needed in this method
            //throw new InvalidGapiIdTokenException;
            $result['errors'][] = $e->getMessage() . ': ' . $e->getTraceAsString();
        }

        // Validate If Expired
        try {
            // If there is no previous token or it's expired.
            $expired = $this->client->isAccessTokenExpired();
            var_dump($expired);
            if ($this->client->isAccessTokenExpired()) {
                // Refresh the token if possible, else fetch a new one.
                if ($refreshToken = $this->client->getRefreshToken()) {
                    var_dump($refreshToken);
                    if($newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken)) {
                        $result['access_token'] = $newToken;
                        $result['is_expired'] = false;
                    }
                }
            }
            // Its Not Expired!
            else {
                $result['is_expired'] = false;
            }
        } catch (\Exception $e) {
            // We actually just want to verify this is true or false
            // If it throws an exception, that means its false, the token is expired
            // This exception can be used for other processes but isn't needed in this method
            //throw new InvalidGapiIdTokenException;
            $result['errors'][] = $e->getMessage() . ': ' . $e->getTraceAsString();
        }

        // Return Payload Results
        return $result;
    }
}
