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
        if(empty(config('oauth.google.app.id'))) {
            throw new MissingGapiClientIdException;
        }

        // Initialize Client
        $client = new Google_Client();
        $client->setApplicationName(config('oauth.google.app.name'));
        $client->setClientId(config('oauth.google.app.id'));
        $client->setClientSecret(config('oauth.google.app.secret'));
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
        // Set Redirect URL
        $client = $this->getClient();
        $client->setRedirectUri($redirectUrl ?? config('oauth.google.redirectUri'));

        // Return Auth URL for Login
        if(empty($scopes)) {
            $scopes = explode(" ", config('oauth.google.scopes'));
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

        // Valid/Expired
        $isValid = $this->validateIdToken($accessToken->id_token);
        $isExpired = $client->isAccessTokenExpired();

        // Try to Refresh Access Token!
        if(!empty($accessToken->refresh_token) && (!$isValid || $isExpired)) {
            $refresh = $this->refreshAccessToken($client);
            if($refresh->exists()) {
                $isValid = $this->validateIdToken($refresh->idToken);
                $isExpired = false;
            }
        }
        if(!$isValid) {
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

        // Valid/Expired
        $isValid = $this->validateIdToken($accessToken->getIdToken());
        $isExpired = $client->isAccessTokenExpired();

        // Try to Refresh Access Token!
        if(!empty($accessToken->getRefreshToken()) && (!$isValid || $isExpired)) {
            $refresh = $this->refreshAccessToken($client);
            if($refresh->exists()) {
                $isValid = $this->validateIdToken($refresh->idToken);
                $isExpired = false;
            }
        }
        if(!$isValid) {
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
     * Validate ID Token
     *
     * @param string $idToken
     * @return boolean
     */
    private function validateIdToken(string $idToken) {
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
            if(!empty($newToken)) {
                $emailToken = EmailToken::fillFromArray($newToken);
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
