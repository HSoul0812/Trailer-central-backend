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
use Illuminate\Support\Collection;
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
     * @const Get Default Scopes
     */
    const DEFAULT_SCOPES = ['openid', 'email', 'profile', 'offline_access'];


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
            'scopes'                  => $this->getScopes($scopes)
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
     * @throws InvalidAzureAuthCodeException
     * @return EmailToken
     */
    public function auth(string $authCode, ?string $redirectUrl = null, ?array $scopes = []): EmailToken {
        // Initialize the OAuth client
        $client = $this->getClient($redirectUrl, $scopes);

        try {
            // Make the token request
            $this->log->info('Scopes on client for auth code: ' . print_r($client->getDefaultScopes(), true));
            $authToken = $client->getAccessToken('authorization_code', [
                'code' => $authCode
            ]);
            $this->log->info('Returned auth token from code: ' . print_r($authToken, true));
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
        $accessToken = new EmailToken();
        $accessToken->fillFromLeague($authToken);
        $this->log->info('Got authorized access token: ' . print_r($accessToken->toArray(), true));

        // Get Profile
        $emailToken = $this->profile($accessToken);

        // Return Email Token
        return $emailToken ?? $accessToken;
    }

    /**
     * Get Azure Profile Email
     *
     * @param CommonToken $accessToken
     * @return null|EmailToken
     */
    public function profile(CommonToken $accessToken): ?EmailToken {
        // Get Graph
        try {
            // Initialize Microsoft Graph
            $graph = new Graph();
            $graph->setAccessToken($accessToken->getAccessToken());

            // Add Email Address From Profile
            $params = $accessToken->toArray();

            // Get Details From Microsoft Account
            $user = $graph->createRequest('GET', '/me')
                ->setReturnType(Model\User::class)
                ->execute();

            // Return Token With Email Address
            $this->log->info('Got response from graph: ' . print_r($user, true));
            $params['first_name'] = $user->getGivenName();
            $params['last_name'] = $user->getSurname();
            $params['email_address'] = $user->getUserPrincipalName();
            $params['folders'] = $this->folders($accessToken);
            $emailToken = new EmailToken($params);
        } catch (\Exception $e) {
            // Log Error
            $this->log->error('Exception returned on getting azure profile email; ' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Azure Token
        return $emailToken ?? null;
    }

    /**
     * Get All Folders for User
     * 
     * @param CommonToken $accessToken
     * @param array $search
     * @return Collection<ImapMailbox>
     */
    public function folders(CommonToken $accessToken, array $search = []): Collection {
        // Initialize Folders Collection
        $folders = new Collection();

        // Get Graph
        try {
            // Initialize Microsoft Graph
            $graph = new Graph();
            $graph->setAccessToken($accessToken->getAccessToken());

            // Get Details From Microsoft Account
            $mailboxes = $graph->createRequest('GET', '/me/mailFolders?top=1000')
                ->setReturnType(Model\MailFolder::class)
                ->execute();

            // Get Full Collection
            foreach($mailboxes as $mailbox) {
                $folders->push(new ImapMailbox([
                    'full' => $mailbox->getDisplayName(),
                    'name' => $mailbox->getDisplayName(),
                    'delimiter' => ImapMailbox::DELIMITER
                ]));
            }
            $this->log->info('Got mail folders from graph: ' . print_r($folders, true));
        } catch (\Exception $e) {
            // Log Error
            $this->log->error('Exception returned on getting azure profile email; ' . $e->getMessage() . ': ' . $e->getTraceAsString());
        }

        // Return Collection of ImapMailbox
        return $folders;
    }

    /**
     * Refresh Access Token
     *
     * @param AccessToken $accessToken
     * @return EmailToken
     */
    public function refresh(AccessToken $accessToken): EmailToken {
        // Initialize Email Token
        $emailToken = new EmailToken();
        $emailToken->fillFromToken($accessToken);

        // Refresh By Custom Now
        return $this->refreshCustom($emailToken);
    }

    /**
     * Refresh Access Token Using Custom Config
     *
     * @param CommonToken $accessToken
     * @return EmailToken
     */
    public function refreshCustom(CommonToken $accessToken): EmailToken {
        // Configure Client
        $client = $this->getClient(null, $accessToken->scopes);

        // Get New Token
        $newToken = $client->getAccessToken('refresh_token', [
            'refresh_token' => $accessToken->refreshToken
        ]);

        // Return Updated EmailToken
        $emailToken = new EmailToken();
        $emailToken->fillFromLeague($newToken);
        $this->log->info('Got refreshed access token: ' . print_r($emailToken->toArray(), true));
        return $emailToken;
    }

    /**
     * Validate Microsoft Azure Access Token Exists and Refresh if Possible
     *
     * @param AccessToken $accessToken
     * @throws MissingAzureIdTokenException
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
        $isValid = ($profile !== null ? true : false);
        $isExpired = ($profile !== null ? $profile->isExpired() : true);

        // Try to Refresh Access Token!
        if($accessToken->refreshToken && (!$isValid || $isExpired)) {
            $refresh = $this->refreshCustom($accessToken);
            if($refresh->exists()) {
                $newProfile = $this->profile($refresh);
                $isValid = ($newProfile !== null ? true : false);
                $isExpired = false;
            }
        }
        if(empty($isValid)) {
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
     * Get Outlook Scopes Including Defaults
     * 
     * @param null|array $scopes
     * @return string
     */
    protected function getScopes(?array $scopes = null): string {
        // Get Scopes
        if(empty($scopes)) {
            if(!empty(config('azure.scopes'))) {
                $scopes = explode(" ", config('azure.scopes'));
            }
        }
        if(empty($scopes)) {
            $scopes = [];
        }

        // Return Final Scopes
        return implode(" ", array_merge(self::DEFAULT_SCOPES, $scopes));
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
