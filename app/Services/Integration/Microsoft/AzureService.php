<?php

namespace App\Services\Integration\Microsoft;

use App\Exceptions\Integration\Microsoft\CannotReceiveAzureProfileException;
use App\Exceptions\Integration\Microsoft\CannotReceiveAzureFoldersException;
use App\Exceptions\Integration\Microsoft\InvalidAzureAuthCodeException;
use App\Exceptions\Integration\Microsoft\MissingAzureIdTokenException;
use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Email\DTOs\ImapMailbox;
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
use Microsoft\Graph\Model\User;
use Microsoft\Graph\Model\MailFolder;

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
    const DEFAULT_SCOPES = ['openid', 'email', 'profile', 'offline_access', 'User.Read'];


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
     * @param array $scopes scopes requested by login
     * @return GenericProvider
     */
    public function getClient(?string $redirectUrl = null, array $scopes = []): GenericProvider {
        // Initialize the OAuth Client
        return new GenericProvider([
            'clientId'                => config('oauth.azure.app.id'),
            'clientSecret'            => config('oauth.azure.app.secret'),
            'redirectUri'             => $redirectUrl ?? config('oauth.azure.redirectUri'),
            'urlAuthorize'            => config('oauth.azure.authority.root').config('oauth.azure.authority.authorize'),
            'urlAccessToken'          => config('oauth.azure.authority.root').config('oauth.azure.authority.token'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => $this->getScopes($scopes)
        ]);
    }

    /**
     * Get Login URL
     *
     * @param null|string $redirectUrl url to redirect auth back to again
     * @param array $scopes scopes requested by login
     * @return LoginUrlToken
     */
    public function login(?string $redirectUrl = null, array $scopes = []): LoginUrlToken {
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
        $accessToken = EmailToken::fillFromLeague($authToken);
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
                ->setReturnType(User::class)
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
            $this->log->error('Exception returned on getting azure profile email; ' . $e->getMessage());
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
                ->setReturnType(MailFolder::class)
                ->execute();

            // Get Full Collection
            foreach($mailboxes as $mailbox) {
                $folders->push(new ImapMailbox([
                    'full' => $mailbox->getDisplayName(),
                    'name' => $mailbox->getDisplayName(),
                    'delimiter' => ImapMailbox::DELIMITER
                ]));
            }
            $this->log->info('Got ' . $folders->count() . ' mail folders from graph');
        } catch (\Exception $e) {
            // Log Error
            $this->log->error('Exception returned on getting azure folders email; ' . $e->getMessage());
            throw new CannotReceiveAzureFoldersException;
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
        $emailToken = EmailToken::fillFromToken($accessToken);

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
        try {
            $newToken = $client->getAccessToken('refresh_token', [
                'refresh_token' => $accessToken->refreshToken
            ]);

            // Return Updated EmailToken
            $emailToken = EmailToken::fillFromLeague($newToken);
            $this->log->info('Got refreshed access token: ' . print_r($emailToken->toArray(), true));
        } catch(\Exception $ex) {
            // Get Exception
            $this->log->error('Failed to refresh access token, response returned: ' . $ex->getMessage());
            $emailToken = new EmailToken();
        }

        // Return Token
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
        $emailToken = EmailToken::fillFromToken($accessToken);

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
     * @param array $scopes
     * @return string
     */
    protected function getScopes(array $scopes = []): string {
        // Get Scopes
        if(empty($scopes)) {
            if(!empty(config('oauth.azure.scopes'))) {
                $scopes = explode(" ", config('oauth.azure.scopes'));
            }
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
