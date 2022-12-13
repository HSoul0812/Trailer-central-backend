<?php

namespace App\Services\Integration\Google;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\LoginUrlToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
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
     * @const array<string>
     */
    const AUTH_TYPES = ['google', 'system'];

    /**
     * @const string
     */
    const AUTH_TYPE_DEFAULT = 'google';
    
    /**
     * @const string
     */
    const AUTH_TYPE_SYSTEM = 'system';


    /**
     * @var string
     */
    protected $type;

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
        // Get Type?
        $type = $this->type ?? self::AUTH_TYPE_DEFAULT;

        // No Client ID?!
        if(empty(config('oauth.' . $type . '.app.id'))) {
            throw new MissingGapiClientIdException;
        }

        // Initialize Client
        $client = new Google_Client();
        $client->setApplicationName(config('oauth.' . $type . '.app.name'));
        $client->setClientId(config('oauth.' . $type . '.app.id'));
        $client->setClientSecret(config('oauth.' . $type . '.app.secret'));
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
     * @param null|string $redirectUrl url to redirect auth back to again
     * @param null|array $scopes scopes requested by login
     * @return LoginUrlToken
     */
    public function login(?string $redirectUrl = null, ?array $scopes = null): LoginUrlToken {
        // Get Type?
        $type = $this->type ?? self::AUTH_TYPE_DEFAULT;

        // Set Redirect URL
        $client = $this->getClient();
        $client->setRedirectUri($redirectUrl ?? config('oauth.' . $type . '.redirectUri'));

        // Return Auth URL for Login
        if(empty($scopes)) {
            $scopes = explode(" ", config('oauth.' . $type . '.scopes'));
        }
        $url = $client->createAuthUrl($scopes);

        // Return LoginUrlToken
        return new LoginUrlToken([
            'loginUrl' => $url
        ]);
    }

    /**
     * Refresh Access Token
     *
     * @param AccessToken $accessToken
     * @return EmailToken
     */
    public function refresh(AccessToken $accessToken): EmailToken {
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
        $newToken = $client->fetchAccessTokenWithRefreshToken($accessToken->refresh_token);
        $commonToken = new EmailToken();
        if(!empty($newToken)) {
            $commonToken = EmailToken::fillFromArray($newToken);
        }

        // Return New Common Token
        return $commonToken;
    }

    /**
     * Validate Google API Access Token Exists and Refresh if Possible
     *
     * @param AccessToken $accessToken
     * @return ValidateToken
     */
    public function validate(AccessToken $accessToken): ValidateToken {
        // Configure Client
        try {
            $client = $this->getClient();
            $client->setAccessToken([
                'access_token' => $accessToken->access_token,
                'refresh_token' => $accessToken->refresh_token,
                'id_token' => $accessToken->id_token,
                'expires_in' => $accessToken->expires_in,
                'created' => strtotime($accessToken->issued_at)
            ]);
            $client->setScopes($accessToken->scope);

            // Valid/Expired
            $isValid = $this->validateIdToken($accessToken->id_token);
            $isExpired = $client->isAccessTokenExpired();
            $this->log->info("Value ID token? {$isValid} | Token Expired? {$isExpired}");

            // Try to Refresh Access Token!
            if(!empty($accessToken->refresh_token) && (!$isValid || $isExpired)) {
                $refresh = $this->refreshAccessToken($client);
                if($refresh->exists()) {
                    $isValid = $this->validateIdToken($refresh->idToken);
                    $isExpired = false;
                    $this->log->info("Successfully refreshed access token for {$accessToken->relation_type} #{$accessToken->relation_id}");
                } else {
                    $this->log->error("Failed to refresh access token for {$accessToken->relation_type} #{$accessToken->relation_id}");
                }
            }

            // Not Valid? Mark Expired
            if(empty($isValid)) {
                $isExpired = true;
            }
        } catch (\Exception $e) {
            $this->log->error('Exception returned validating custom google oauth: ' . $e->getMessage());
            $isValid = false;
            $isExpired = true;
        }

        // Return Payload Results
        return new ValidateToken([
            'new_token' => $refresh ?? null,
            'is_valid' => $isValid,
            'is_expired' => $isExpired,
            'message' => $this->getValidateMessage($isValid, $isExpired)
        ]);
    }

    /**
     * Validate Google API Access Token Exists and Refresh if Possible
     *
     * @param CommonToken $accessToken
     * @return ValidateToken
     */
    public function validateCustom(CommonToken $accessToken): ValidateToken {
        // Configure Client
        try {
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
            $accessTokenString = json_encode($accessToken);

            // Valid/Expired
            $isValid = $this->validateIdToken($accessToken->getIdToken());
            $isExpired = $client->isAccessTokenExpired();
            $this->log->info("Value ID token? {$isValid} | Token Expired? {$isExpired}");

            // Try to Refresh Access Token!
            if(!empty($accessToken->getRefreshToken()) && (!$isValid || $isExpired)) {
                $refresh = $this->refreshAccessToken($client);
                if($refresh->exists()) {
                    $isValid = $this->validateIdToken($refresh->idToken);
                    $isExpired = false;
                    $this->log->info("Successfully refreshed access token for $accessTokenString");
                } else {
                    $this->log->error("Failed to refresh access token for $accessTokenString");
                }
            }
            if(!$isValid) {
                $isExpired = true;
            }
        } catch (\Exception $e) {
            $this->log->error('Exception returned validating custom google oauth: ' . $e->getMessage());
            $isValid = false;
            $isExpired = true;
        }
        // Return Payload Results
        return new ValidateToken([
            'new_token' => $refresh ?? null,
            'is_valid' => $isValid,
            'is_expired' => $isExpired,
            'message' => $this->getValidateMessage($isValid, $isExpired)
        ]);
    }

    /**
     * Set Key for Google Service
     * 
     * @param string $key
     * @return string
     */
    public function setKey(string $key = ''): string {
        // Key Type is Valid?
        if(!in_array($key, self::AUTH_TYPES)) {
            return self::AUTH_TYPE_DEFAULT;
        }

        // Set Key on Google App
        return $this->type = $key;
    }


    /**
     * Validate ID Token
     *
     * @param null|string $idToken
     * @return boolean
     */
    private function validateIdToken(?string $idToken) {
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
            $this->log->error('Exception returned for Google ID Token:' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Validate
        return $validate;
    }

    /**
     * Refresh Access Token
     *
     * @param Google_Client $client
     * @return EmailToken
     */
    private function refreshAccessToken(Google_Client $client): EmailToken {
        // Initialize Email Token
        $emailToken = new EmailToken();

        // Validate If Expired
        try {
            // Refresh the token if possible, else fetch a new one.
            $refreshToken = $client->getRefreshToken();
            $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            if(!empty($newToken) && isset($newToken['access_token'])) {
                $emailToken = EmailToken::fillFromArray($newToken);
            } elseif(isset($newToken['error'])) {
                $this->log->error('FATAL ERROR refreshing access token: ' . $newToken['error_description']);
            }
        } catch (\Exception $e) {
            // We actually just want to verify this is true or false
            // If it throws an exception, that means its false, the token isn't valid
            $this->log->error('Exception returned for Google Refresh Access Token: ' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Token
        return $emailToken;
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
                return 'Your Google Authorization has expired! Please try connecting again.';
            } else {
                return 'Your Google Authorization has been validated successfully!';
            }
        }
        return 'Your Google Authorization failed! Please try connecting again.';
    }
}
