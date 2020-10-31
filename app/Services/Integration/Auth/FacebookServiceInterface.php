<?php

namespace App\Services\Integration\Auth;

interface FacebookServiceInterface {
    /**
     * Request Facebook SDK Access Token Exists
     * 
     * @param array $scopes
     * @return array of validation info
     */
    public function request($scopes);

    /**
     * Validate Facebook SDK Access Token Exists
     * 
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function validate($accessToken);
}