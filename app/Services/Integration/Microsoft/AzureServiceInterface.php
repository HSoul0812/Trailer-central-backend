<?php

namespace App\Services\Integration\Microsoft;

use App\Services\Integration\Common\DTOs\CommonToken;
use League\OAuth2\Client\Provider\GenericProvider;

interface AzureServiceInterface {
    /**
     * Get Client
     *
     * @param null|string $redirectUrl url to redirect auth back to again
     * @param null|array $scopes scopes requested by login
     * @return GenericProvider
     */
    public function getClient(?string $redirectUrl = null, ?array $scopes = null): GenericProvider;

    /**
     * Get Login URL
     *
     * @param null|string $redirectUrl url to redirect auth back to again
     * @param null|array $scopes scopes requested by login
     * @return array{url: string, state: object}
     */
    public function login(?string $redirectUrl = null, ?array $scopes = null): array;

    /**
     * Use Authorize Code to Get Tokens
     *
     * @param string $authCode
     * @param null|string $redirectUrl url to redirect auth back to again
     * @param null|array $scopes scopes requested by login
     * @return array created from EmailTokenTransformer
     */
    public function auth(string $authCode, ?string $redirectUrl = null, ?array $scopes = []): array;

    /**
     * Get Refresh Token
     * 
     * @param array $accessToken
     * @return array of validation info
     */
    public function refresh($accessToken);

    /**
     * Validate Google API Access Token Exists
     * 
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function validate($accessToken);

    /**
     * Validate Google API Access Token Exists and Refresh if Possible
     * 
     * @param CommonToken $accessToken
     * @return array of validation info
     */
    public function validateCustom(CommonToken $accessToken);
}