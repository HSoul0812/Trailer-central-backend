<?php

namespace App\Services\Marketing\Craigslist\DTOs;

use App\Models\Marketing\Craigslist\Profile;
use App\Services\Marketing\Craigslist\DTOs\ProfileAccount;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ProfileAccounts
 * 
 * @package App\Services\Marketing\Craigslist\DTOs
 */
class ProfileAccounts
{
    use WithConstructor, WithGetter;


    /**
     * @var Collection<Profile>
     */
    private $profiles;

    /**
     * @var Collection<ProfileAccount>
     */
    private $accounts;
}