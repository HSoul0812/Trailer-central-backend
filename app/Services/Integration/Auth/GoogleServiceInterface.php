<?php

namespace App\Services\Integration\Auth;

interface GoogleServiceInterface {
    /**
     * Validate Google API Access Token Exists
     * 
     * @param AccessToken $accessToken
     * @return array of validation info
     */
    public function validate($accessToken);
}