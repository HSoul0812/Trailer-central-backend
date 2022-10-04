<?php

namespace App\Services\Dispatch\Facebook;

use App\Services\Dispatch\Facebook\DTOs\MarketplaceStatus;

interface MarketplaceServiceInterface {
    /**
     * Login to Marketplace
     * 
     * @param string $uuid
     * @param string $ip
     * @param string $version
     * @return string
     */
    public function login(string $uuid, string $ip, string $version): string;

    /**
     * Get Marketplace Status
     * 
     * @return MarketplaceStatus
     */
    public function status(array $params): MarketplaceStatus;
}