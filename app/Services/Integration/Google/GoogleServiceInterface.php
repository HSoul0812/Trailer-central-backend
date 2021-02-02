<?php

namespace App\Services\Integration\Google;

use App\Services\Integration\Google\DTOs\CommonToken;

interface GoogleServiceInterface {
    /**
     * Get Login URL
     * 
     * @param string $redirectUrl url to redirect auth back to again
     * @param array $scopes scopes requested by login
     * @return login url with offline access support
     */
    public function login($redirectUrl, $scopes);

    /**
     * Get Auth URL
     * 
     * @param string $redirectUrl url to redirect auth back to again
     * @param string $authCode auth code to get full credentials with
     * @return array created from GoogleTokenTransformer
     */
    public function auth($redirectUrl, $authCode): array;

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