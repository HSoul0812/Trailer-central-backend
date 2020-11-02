<?php

namespace App\Services\Integration\Auth;

interface FacebookServiceInterface {
    /**
     * Validate Facebook SDK Access Token Exists
     * 
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function validate($accessToken);
}