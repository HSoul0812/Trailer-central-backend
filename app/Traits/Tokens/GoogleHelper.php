<?php

namespace App\Traits\Tokens;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Google\GoogleServiceInterface;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;

/**
 * Trait Google
 * @package App\Traits\Tokens
 */
trait GoogleHelper
{
    /**
     * Refresh Gmail Access Token
     * 
     * @param AccessToken $accessToken
     * @param GoogleServiceInterface $googleService
     * @param TokenServiceInterface $tokenService
     * @return AccessToken
     */
    private function refreshAccessToken(
        AccessToken $accessToken,
        GoogleServiceInterface $googleService,
        TokenRepositoryInterface $tokenService
    ): AccessToken {
        // Refresh Token
        $validate = $googleService->validate($accessToken);
        if(!empty($validate['new_token'])) {
            $accessToken = $tokenService->refresh($accessToken->id, $validate['new_token']);
        }

        // Return New Token
        return $accessToken;
    }
}