<?php

namespace App\Services\Dispatch\Facebook;

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
}