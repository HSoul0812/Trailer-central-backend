<?php

namespace App\Services\Marketing\Craigslist\DTOs;

use App\Models\Marketing\Craigslist\Profile;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Illuminate\Support\Collection;

/**
 * Class Account
 * 
 * @package App\Services\Marketing\Craigslist\DTOs
 */
class Account
{
    use WithConstructor, WithGetter;


    /**
     * @var string
     */
    private $username;

    /**
     * @var Collection<Profile>
     */
    private $profiles;
}