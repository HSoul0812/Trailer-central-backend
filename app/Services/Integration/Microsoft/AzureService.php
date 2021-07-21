<?php

namespace App\Services\Integration\Microsoft;

use App\Services\Integration\Common\DTOs\CommonToken;
use App\Exceptions\Integration\Microsoft\MissingGapiIdTokenException;
use Microsoft_Client;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\GenericProvider;

/**
 * Class AzureService
 *
 * @package App\Services\Integration\Microsoft
 */
class AzureService implements AzureServiceInterface
{
    /**
     * Create Microsoft Log
     */
    public function __construct()
    {
        // Initialize Logger
        $this->log = Log::channel('microsoft');
    }


    /**
     * Get Login URL
     *
     * @param null|string $redirectUrl url to redirect auth back to again
     * @param null|array $scopes scopes requested by login
     * @return array{url: string, state: object}
     */
    public function login(?string $redirectUrl = null, ?array $scopes = null): array {
        // Initialize the OAuth client
        $oauthClient = new GenericProvider([
            'clientId'                => config('azure.app.id'),
            'clientSecret'            => config('azure.app.secret'),
            'redirectUri'             => $redirectUrl ?? config('azure.redirectUri'),
            'urlAuthorize'            => config('azure.authority.root').config('azure.authority.authorize'),
            'urlAccessToken'          => config('azure.authority.root').config('azure.authority.token'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => $scopes ?? config('azure.scopes')
        ]);

        // Return Array of Results
        return [
            'url' => $oauthClient->getAuthorizationUrl(),
            'state' => $oauthClient->getState()
        ];
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
     * Validate Microsoft API Access Token Exists and Refresh if Possible
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
            'is_expired' => $client->isAccessTokenExpired(),
            'message' => ''
        ];

        // Try to Refesh Access Token!
        if(!empty($accessToken->refresh_token) && (!$result['is_valid'] || $result['is_expired'])) {
            $refresh = $this->refreshAccessToken($client);
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

        // Get Message
        $result['message'] = $this->getValidateMessage($result['is_valid'], $result['is_expired']);

        // Return Payload Results
        return $result;
    }

    /**
     * Validate Microsoft API Access Token Exists and Refresh if Possible
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
            'is_expired' => $client->isAccessTokenExpired(),
            'message' => ''
        ];

        // Try to Refesh Access Token!
        if(!empty($accessToken->getRefreshToken()) && (!$result['is_valid'] || $result['is_expired'])) {
            $refresh = $this->refreshAccessToken($client);
            $result['is_expired'] = $refresh['expired'];
            if(!empty($refresh['access_token'])) {
                unset($refresh['expired']);
                $result['is_valid'] = $this->validateIdToken($refresh['id_token']);
                $result['new_token'] = $refresh;
            }
        }

        // Get Message
        $result['message'] = $this->getValidateMessage($result['is_valid'], $result['is_expired']);

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
            $this->log->error('Exception returned for Microsoft Access Token:' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Validate
        return $validate;
    }

    /**
     * Refresh Access Token
     *
     * @param Microsoft_Client $client
     * @return array of expired status, also return new token if available
     */
    private function refreshAccessToken(Microsoft_Client $client) {
        // Set Expired
        $result = [
            'new_token' => [],
            'expired' => true
        ];

        // Validate If Expired
        try {
            // Refresh the token if possible, else fetch a new one.
            if ($refreshToken = $client->getRefreshToken()) {
                if($newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken)) {
                    $result = $newToken;
                    $result['expired'] = false;
                }
            }
        } catch (\Exception $e) {
            // We actually just want to verify this is true or false
            // If it throws an exception, that means its false, the token isn't valid
            $this->log->error('Exception returned for Microsoft Refresh Access Token: ' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Result
        return $result;
    }

    /**
     * Get Validation Message
     * 
     * @param bool $valid
     * @param bool $expired
     * @return string
     */
    private function getValidateMessage(bool $valid = false, bool $expired = false): string {
        // Return Validation Message
        if(!empty($valid)) {
            if(!empty($expired)) {
                return 'Your Microsoft Azure Authorization has expired! Please try connecting again.';
            } else {
                return 'Your Microsoft Azure Authorization has been validated successfully!';
            }
        }
        return 'Your Microsoft Azure Authorization failed! Please try connecting again.';
    }
}
