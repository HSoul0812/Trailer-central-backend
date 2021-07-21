<?php

namespace App\Services\Integration\Google;

use App\Services\Integration\Common\DTOs\CommonToken;
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
     * @return login url with offline access support
     */
    public function login($redirectUrl, $scopes);

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