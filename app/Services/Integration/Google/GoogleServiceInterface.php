<?php

namespace App\Services\Integration\Google;

use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
use Google_Client;

interface GoogleServiceInterface {
    /**
     * Get Fresh Client
     * 
     * @throws MissingGapiClientIdException
     * @throws FailedConnectGapiClientException
     * @return Google_Client
     */
    public function getClient(): Google_Client;

    /**
     * Get Login URL
     *
     * @param string $redirectUrl url to redirect auth back to again
     * @param array $scopes scopes requested by login
     * @return string login url with offline access support
     */
    public function login(string $redirectUrl, array $scopes): string;

    /**
     * Get Refresh Token
     *
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function refresh(AccessToken $accessToken): array;

    /**
     * Validate Google API Access Token Exists and Refresh if Possible
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