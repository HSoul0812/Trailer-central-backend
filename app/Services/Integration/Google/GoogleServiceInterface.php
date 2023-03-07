<?php

namespace App\Services\Integration\Google;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Services\Integration\Common\DTOs\LoginUrlToken;
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
     * @param null|string $redirectUrl url to redirect auth back to again
     * @param null|array $scopes scopes requested by login
     * @return LoginUrlToken
     */
    public function login(?string $redirectUrl = null, ?array $scopes = null): LoginUrlToken;

    /**
     * Refresh Access Token
     *
     * @param AccessToken $accessToken
     * @return EmailToken
     */
    public function refresh(AccessToken $accessToken): EmailToken;

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

    /**
     * Set Key for Google Service
     * 
     * @param string $key
     * @return string
     */
    public function setKey(string $key = ''): string;
}