<?php

namespace App\Services\Integration\Google;

use App\Exceptions\Integration\Google\MissingGapiAccessTokenException;
use App\Exceptions\Integration\Google\MissingGapiIdTokenException;
use App\Exceptions\Integration\Google\MissingGapiClientIdException;
use App\Exceptions\Integration\Google\InvalidGapiIdTokenException;
use App\Exceptions\Integration\Google\FailedConnectGapiClientException;
use Illuminate\Support\Facades\Log;

/**
 * Class GoogleService
 * 
 * @package App\Services\Integration\Google
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
            'client_id' => $_ENV['GOOGLE_OAUTH_CLIENT_ID'],
            'client_secret' => $_ENV['GOOGLE_OAUTH_CLIENT_SECRET']
        ]);
        if(empty($this->client)) {
            throw new FailedConnectGapiClientException;
        }

        // Set Redirect URL
        $this->client->setRedirectUri($_ENV['GOOGLE_OAUTH_REDIRECT_URI']);
        $this->client->setAccessType('offline');
        $this->client->setIncludeGrantedScopes(true);
    }


    /**
     * Get Login URL
     * 
     * @param string $redirectUrl url to redirect auth back to again
     * @param array $scopes scopes requested by login
     * @return login url with offline access support
     */
    public function login($redirectUrl, $scopes) {
        // Set Redirect URL
        $this->client->setRedirectUri($redirectUrl);

        // Return Auth URL for Login
        return $this->client->createAuthUrl($scopes);
    }

    /**
     * Get Auth URL
     * 
     * @param string $authCode auth code to get full credentials with
     * @return all auth data
     */
    public function auth($authCode) {
        echo $this->client->setRedirectUri();
        return $this->client->fetchAccessTokenWithAuthCode($authCode);
    }

    /**
     * Get Refresh Token
     * 
     * @param array $params
     * @return array of validation info
     */
    public function refresh($accessToken) {
        // Configure Client
        $this->client->setAccessToken([
            'access_token' => $accessToken->access_token,
            'id_token' => $accessToken->id_token,
            'expires_in' => $accessToken->expires_in,
            'created' => strtotime($accessToken->issued_at)
        ]);
        $this->client->setScopes($accessToken->scope);

        // Return Refresh Token
        return $this->client->getRefreshToken();
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
            'is_valid' => $this->validateIdToken($accessToken->id_token),
            'is_expired' => true
        ];

        // Only if Valid!
        if(!empty($result['is_valid'])) {
            $refresh = $this->refreshAccessToken();
            $result['is_expired'] = $refresh['expired'];
            if(isset($refresh['access_token'])) {
                $result['access_token'] = $refresh['access_token'];
            }
        }

        // Return Payload Results
        return $result;
    }


    /**
     * Validate ID Token
     * 
     * @param AccessToken $accessToken
     * @return boolean
     */
    private function validateIdToken($accessToken) {
        // Invalid
        $validate = false;

        // Validate ID Token
        try {
            // Verify ID Token is Valid
            $payload = $this->client->verifyIdToken($accessToken->id_token);
            if ($payload) {
                $validate = true;
            }
        }
        catch (\Exception $e) {
            // We actually just want to verify this is true or false
            // If it throws an exception, that means its false, the token isn't valid
            Log::error('Exception returned for Google Access Token:' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Validate
        return $validate;
    }

    /**
     * Refresh Access Token
     * 
     * @return array of expired status, also return new token if available
     */
    private function refreshAccessToken() {
        // Set Expired
        $result = [
            'expired' => true
        ];

        // Validate If Expired
        try {
            // If there is no previous token or it's expired.
            $this->client->isAccessTokenExpired();
            if ($this->client->isAccessTokenExpired()) {
                // Refresh the token if possible, else fetch a new one.
                if ($refreshToken = $this->client->getRefreshToken()) {
                    if($newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken)) {
                        $result['access_token'] = $newToken;
                        $result['expired'] = false;
                    }
                }
            }
            // Its Not Expired!
            else {
                $result['expired'] = false;
            }
        } catch (\Exception $e) {
            // We actually just want to verify this is true or false
            // If it throws an exception, that means its false, the token isn't valid
            Log::error('Exception returned for Google Refresh Access Token:' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Result
        return $result;
    }
}
