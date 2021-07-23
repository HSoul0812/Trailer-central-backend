<?php

namespace App\Services\Integration\Microsoft;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
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
     * @return EmailToken
     */
    public function auth(string $authCode, ?string $redirectUrl = null, ?array $scopes = []): EmailToken;

    /**
     * Get Azure Profile Email
     *
     * @param EmailToken $emailToken
     * @return EmailToken
     */
    public function profile(EmailToken $emailToken): EmailToken;

    /**
     * Get Refresh Token
     *
     * @param AccessToken $accessToken
     * @return EmailToken
     */
    public function refresh(AccessToken $accessToken): EmailToken;

    /**
     * Validate Microsoft Azure Access Token Exists and Refresh if Possible
     *
     * @param AccessToken $accessToken
     * @return ValidateToken
     */
    public function validate(AccessToken $accessToken): ValidateToken;

    /**
     * Validate Google API Access Token Exists and Refresh if Possible
     * 
     * @param CommonToken $accessToken
     * @return ValidateToken
     */
    public function validateCustom(CommonToken $accessToken): ValidateToken;
}