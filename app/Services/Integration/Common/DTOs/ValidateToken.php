<?php

namespace App\Services\Integration\Common\DTOs;

use App\Models\Integration\Auth\AccessToken;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ValidateToken
 * 
 * @package App\Services\Integration\Common\DTOs
 */
class ValidateToken
{
    use WithConstructor, WithGetter;

    /**
     * @var EmailToken Replacement Access Token Details
     */
    private $newToken;

    /**
     * @var string Temporary Local Filename
     */
    private $isValid;

    /**
     * @var string Path to Current File
     */
    private $isExpired;

    /**
     * @var string Message Response to Return
     */
    private $message;

    /**
     * @var AccessToken Replacement Access Token
     */
    private $accessToken;


    /**
     * Set New Access Token
     * 
     * @param AccessToken $accessToken
     * @return void
     */
    public function setAccessToken(AccessToken $accessToken): void {
        $this->accessToken = $accessToken;
    }
}