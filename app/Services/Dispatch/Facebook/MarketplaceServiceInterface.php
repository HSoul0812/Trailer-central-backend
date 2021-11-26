<?php

namespace App\Services\Dispatch\Facebook;

interface MarketplaceServiceInterface {
    /**
     * Login to Marketplace
     * 
     * @return string
     */
    public function login(): string;
}