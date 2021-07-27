<?php

namespace App\Services\Integration\Microsoft;

use App\Exceptions\Integration\Microsoft\InvalidAzureAuthCodeException;
use App\Exceptions\Integration\Microsoft\MissingAzureIdTokenException;
use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Services\Integration\Common\DTOs\LoginUrlToken;
use App\Utilities\Fractal\NoDataArraySerializer;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

/**
 * Class AzureService
 *
 * @package App\Services\Integration\Microsoft
 */
class AzureService implements AzureServiceInterface
{
    /**
     * Create Microsoft Azure Log
     */
    public function __construct(Manager $fractal)
    {
        // Initialize Services
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());

        // Initialize Logger
        $this->log = Log::channel('azure');
    }


    /**
     * Get Client
     *
     * @param null|string $redirectUrl url to redirect auth back to again
     * @param null|array $scopes scopes requested by login
     * @return GenericProvider
     */
    public function getClient(?string $redirectUrl = null, ?array $scopes = null): GenericProvider {
        // Initialize the OAuth Client
        $authClient = new GenericProvider([
            'clientId'                => config('azure.app.id'),
            'clientSecret'            => config('azure.app.secret'),
            'redirectUri'             => $redirectUrl ?? config('azure.redirectUri'),
            'urlAuthorize'            => config('azure.authority.root').config('azure.authority.authorize'),
            'urlAccessToken'          => config('azure.authority.root').config('azure.authority.token'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => $scopes ?? config('azure.scopes')
        ]);

        // Return Auth Client
        return $authClient;
    }

    /**
     * Get Login URL
     *
     * @param null|string $redirectUrl url to redirect auth back to again
     * @param null|array $scopes scopes requested by login
     * @return LoginUrlToken
     */
    public function login(?string $redirectUrl = null, ?array $scopes = null): LoginUrlToken {
        // Initialize the OAuth client
        $client = $this->getClient($redirectUrl, $scopes);

        // Return LoginUrlToken
        return new LoginUrlToken([
            'loginUrl' => $client->getAuthorizationUrl(),
            'authState' => $client->getState()
        ]);
    }

    /**
     * Use Authorize Code to Get Tokens
     *
     * @param string $authCode
     * @param null|string $redirectUrl url to redirect auth back to again
     * @param null|array $scopes scopes requested by login
     * @return EmailToken
     */
    public function auth(string $authCode, ?string $redirectUrl = null, ?array $scopes = []): EmailToken {
        // Initialize the OAuth client
        $client = $this->getClient($redirectUrl, $scopes);

        try {
            // Make the token request
            $authToken = $client->getAccessToken('authorization_code', [
                'code' => $authCode
            ]);
        } catch (IdentityProviderException $e) {
            $response = $e->getResponseBody();
            $this->log->error('IdentityProviderException returned ' . $e->getMessage() .
                                ' on AzureService: ' . ($response['error_description'] ?? 'unavailable'));
            throw new InvalidAzureAuthCodeException;
        } catch (\Exception $e) {
            $this->log->error('Unknown Exception returned on AzureService: ' . $e->getMessage());
            throw new InvalidAzureAuthCodeException;
        }

        // Return Formatted Auth Token
        $emailToken = new EmailToken();
        $emailToken->fillFromLeague($authToken);

        // Get Profile
        $this->profile($emailToken);

        // Return Email Token
        return $emailToken;
    }

    /**
     * Get Azure Profile Email
     *
     * @param EmailToken $emailToken
     * @return EmailToken
     */
    public function profile(EmailToken $emailToken): EmailToken {
        // Get Graph
        try {
            // Initialize Microsoft Graph
            $graph = new Graph();
            $graph->setAccessToken($emailToken->accessToken);

            // Get Details From Microsoft Account
            $user = $graph->createRequest('GET', '/me?$select=mail')
                ->setReturnType(Model\User::class)
                ->execute();

            // Append Profile
            $emailToken->setEmailAddress($user->getUserPrincipalName());
        } catch (\Exception $e) {
            // Log Error
            $this->log->error('Exception returned on getting azure profile email; ' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Azure Token
        return $emailToken;
    }

    /**
     * Get Refresh Token
     *
     * @param AccessToken $accessToken
     * @return EmailToken
     */
    public function refresh(AccessToken $accessToken): EmailToken {
        // Configure Client
        $client = $this->getClient();

        // Get New Token
        return $client->getAccessToken('refresh_token', [
            'refresh_token' => $accessToken->refresh_token
        ]);
    }

    /**
     * Validate Microsoft Azure Access Token Exists and Refresh if Possible
     *
     * @param AccessToken $accessToken
     * @return ValidateToken
     */
    public function validate(AccessToken $accessToken): ValidateToken {
        // ID Token Exists?
        if(empty($accessToken->id_token)) {
            throw new MissingAzureIdTokenException;
        }

        // Initialize Email Token
        $emailToken = new EmailToken();
        $emailToken->fillFromToken($accessToken);

        // Validate By Custom Now
        return $this->validateCustom($emailToken);
    }

    /**
     * Validate Microsoft Azure Access Token Exists and Refresh if Possible
     *
     * @param CommonToken $accessToken
     * @return ValidateToken
     */
    public function validateCustom(CommonToken $accessToken): ValidateToken {
        // Configure Client
        $profile = $this->profile($accessToken);

        // Valid/Expired
        $isValid = ($profile->emailAddress ? true : false);
        $isExpired = strtotime($accessToken->expiresAt) > time();

        // Try to Refresh Access Token!
        if(!empty($accessToken->refreshToken) && (!$isValid || $isExpired)) {
            $refresh = $this->refresh($accessToken);
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
    private function validateIdToken(string $idToken): bool {
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
     * @return EmailToken
     */
    private function refreshAccessToken(Microsoft_Client $client): EmailToken {
        // Initialize Result
        $result = [];

        // Validate If Expired
        try {
            // Refresh the token if possible, else fetch a new one.
            if ($refreshToken = $client->getRefreshToken()) {
                if($newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken)) {
                    $result = $newToken;
                }
            }
        } catch (\Exception $e) {
            // We actually just want to verify this is true or false
            // If it throws an exception, that means its false, the token isn't valid
            $this->log->error('Exception returned for Microsoft Refresh Access Token: ' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Result
        return new EmailToken($result);
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
