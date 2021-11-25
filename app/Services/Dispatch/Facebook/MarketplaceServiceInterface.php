<?php

namespace App\Services\Dispatch\Facebook;

use App\Models\User\AuthToken;

interface MarketplaceServiceInterface {
    /**
     * Login to Marketplace
     * 
     * Return Auth Token
     */
    public function login(): AuthToken;
}