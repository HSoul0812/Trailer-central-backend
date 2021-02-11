<?php

namespace App\Services\Integration\Google;

use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Exceptions\Integration\Google\MissingGapiIdTokenException;
use App\Exceptions\Integration\Google\MissingGapiClientIdException;
use App\Exceptions\Integration\Google\FailedConnectGapiClientException;
use Google_Client;
use Illuminate\Support\Facades\Log;

/**
 * Class GoogleService
 *
 * @package App\Services\Integration\Google
 */
class GoogleService implements GoogleServiceInterface
{
    /**
     * @var GmailServiceInterface
     */
    protected $gmail;

    /**
     * Construct Google Client
     */
    public function __construct()
    {
        // Initialize Logger
        $this->log = Log::channel('google');
    }


    /**
     * Get Fresh Client
     * 
     * @throws MissingGapiClientIdException
     * @throws FailedConnectGapiClientException
     * @return Google_Client
     */
    public function getClient(): Google_Client {
        // No Client ID?!
        if(empty(env('GOOGLE_OAUTH_CLIENT_ID'))) {
            throw new MissingGapiClientIdException;
        }

        // Initialize Client
        $client = new Google_Client();
        $client->setApplicationName(env('GOOGLE_OAUTH_APP_NAME'));
        $client->setClientId(env('GOOGLE_OAUTH_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_OAUTH_CLIENT_SECRET'));
        if(empty($client)) {
            throw new FailedConnectGapiClientException;
        }

        // Set Defaults
        $client->setAccessType('offline');
        $client->setIncludeGrantedScopes(true);
        return $client;
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
        $client = $this->getClient();
        $client->setRedirectUri($redirectUrl);

        // Return Auth URL for Login
        return $client->createAuthUrl($scopes);
    }

    /**
     * Get Refresh Token
     *
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function refresh($accessToken) {
        // Configure Client
        $client = $this->getClient();
        $client->setAccessToken([
            'access_token' => $accessToken->access_token,
            'refresh_token' => $accessToken->refresh_token,
            'id_token' => $accessToken->id_token,
            'expires_in' => $accessToken->expires_in,
            'created' => strtotime($accessToken->issued_at)
        ]);
        $client->setScopes($accessToken->scope);

        // Get New Token
        return $client->fetchAccessTokenWithRefreshToken($accessToken->refresh_token);
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
        $client = $this->getClient();
        $client->setAccessToken([
            'access_token' => $accessToken->access_token,
            'refresh_token' => $accessToken->refresh_token,
            'id_token' => $accessToken->id_token,
            'expires_in' => $accessToken->expires_in,
            'created' => strtotime($accessToken->issued_at)
        ]);
        $client->setScopes($accessToken->scope);

        // Initialize Vars
        $result = [
            'new_token' => [],
            'is_valid' => $this->validateIdToken($accessToken->id_token),
            'is_expired' => $client->isAccessTokenExpired()
        ];

        // Try to Refesh Access Token!
        if(!empty($accessToken->refresh_token) && (!$result['is_valid'] || $result['is_expired'])) {
            $refresh = $this->refreshAccessToken();
            $result['is_expired'] = $refresh['expired'];
            if(!empty($refresh['access_token'])) {
                unset($refresh['expired']);
                $result['is_valid'] = $this->validateIdToken($refresh['id_token']);
                $result['new_token'] = $refresh;
            }
        }

        // Not Valid?
        if(empty($result['is_valid'])) {
            $result['is_expired'] = true;
        }

        // Return Payload Results
        return $result;
    }

    /**
     * Validate Google API Access Token Exists and Refresh if Possible
     *
     * @param CommonToken $accessToken
     * @return array of validation info
     */
    public function validateCustom(CommonToken $accessToken) {
        // ID Token Exists?
        if(empty($accessToken->getIdToken())) {
            throw new MissingGapiIdTokenException;
        }

        // Configure Client
        $client = $this->getClient();
        $client->setAccessToken([
            'access_token' => $accessToken->getAccessToken(),
            'refresh_token' => $accessToken->getRefreshToken(),
            'id_token' => $accessToken->getIdToken(),
            'expires_in' => $accessToken->getExpiresIn(),
            'created' => $accessToken->getIssuedUnix()
        ]);
        $client->setScopes($accessToken->getScope());

        // Initialize Vars
        $result = [
            'new_token' => [],
            'is_valid' => $this->validateIdToken($accessToken->getIdToken()),
            'is_expired' => $client->isAccessTokenExpired()
        ];

        // Try to Refesh Access Token!
        if(!empty($accessToken->getRefreshToken()) && (!$result['is_valid'] || $result['is_expired'])) {
            $refresh = $this->refreshAccessToken();
            $result['is_expired'] = $refresh['expired'];
            if(!empty($refresh['access_token'])) {
                unset($refresh['expired']);
                $result['is_valid'] = $this->validateIdToken($refresh['id_token']);
                $result['new_token'] = $refresh;
            }
        }

        // Return Payload Results
        return $result;
    }


    /**
     * Validate ID Token
     *
     * @param string $idToken
     * @return boolean
     */
    private function validateIdToken($idToken) {
        // Invalid
        $validate = false;

        // Validate ID Token
        try {
            // Verify ID Token is Valid
            $client = $this->getClient();
            $payload = $client->verifyIdToken($idToken);
            if ($payload) {
                $validate = true;
            }
        }
        catch (\Exception $e) {
            // We actually just want to verify this is true or false
            // If it throws an exception, that means its false, the token isn't valid
            $this->log->error('Exception returned for Google Access Token:' . $e->getMessage() . ': ' . $e->getTraceAsString());
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
            'new_token' => [],
            'expired' => true
        ];

        // Validate If Expired
        try {
            // Refresh the token if possible, else fetch a new one.
            $client = $this->getClient();
            if ($refreshToken = $client->getRefreshToken()) {
                if($newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken)) {
                    $result = $newToken;
                    $result['expired'] = false;
                }
            }
        } catch (\Exception $e) {
            // We actually just want to verify this is true or false
            // If it throws an exception, that means its false, the token isn't valid
            $this->log->error('Exception returned for Google Refresh Access Token: ' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Result
        return $result;
    }
}
