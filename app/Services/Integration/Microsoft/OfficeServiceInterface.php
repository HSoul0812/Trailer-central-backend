<?php

namespace App\Services\Integration\Microsoft;

use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Services\Integration\Common\DTOs\LoginUrlToken;

interface OfficeServiceInterface extends AzureServiceInterface {
    /**
     * Validate Office Access Token Exists and Refresh if Possible
     *
     * @param AccessToken $accessToken
     * @throws MissingAzureIdTokenException
     * @return ValidateToken
     */
    public function validate(AccessToken $accessToken): ValidateToken;

    /**
     * Validate Office Access Token Exists and Refresh if Possible
     * 
     * @param CommonToken $accessToken
     * @return ValidateToken
     */
    public function validateCustom(CommonToken $accessToken): ValidateToken;
}