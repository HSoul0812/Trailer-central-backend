<?php

namespace App\Services\Dispatch\Craigslist;

use Illuminate\Support\Collection;

interface CraigslistServiceInterface {
    /**
     * Login to Craigslist
     * 
     * @param string $uuid
     * @param string $ip
     * @param string $version
     * @return string
     */
    public function login(string $uuid, string $ip, string $version): string;

    /**
     * Get Dealer Craigslist Status
     * 
     * @return Collection<DealerCraigslist>
     */
    public function status(array $params): Collection;
}