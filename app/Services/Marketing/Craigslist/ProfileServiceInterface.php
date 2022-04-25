<?php

namespace App\Services\Marketing\Craigslist;

use App\Services\Marketing\Craigslist\DTOs\ProfileAccounts;

interface ProfileServiceInterface {
    /**
     * Get Profiles
     * 
     * @param array $params
     * @return ProfileAccounts
     */
    public function profiles(array $params): ProfileAccounts;
}