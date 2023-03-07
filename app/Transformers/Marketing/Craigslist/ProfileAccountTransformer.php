<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Services\Marketing\Craigslist\DTOs\ProfileAccounts;
use App\Transformers\Marketing\Craigslist\ProfileTransformer;
use App\Transformers\Marketing\Craigslist\AccountProfileTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class ProfileAccountTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class ProfileAccountTransformer extends TransformerAbstract
{ 
    protected $defaultIncludes = [
        'profiles',
        'accounts'
    ];

    /**
     * @var ProfileTransformer
     */
    protected $profileTransformer;

    /**
     * @var AccountProfileTransformer
     */
    protected $accountTransformer;

    public function __construct(
        ProfileTransformer $profileTransformer,
        AccountProfileTransformer $accountTransformer
    ) {
        $this->profileTransformer = $profileTransformer;
        $this->accountTransformer = $accountTransformer;
    }

    public function transform(ProfileAccounts $account)
    {
        return [];
    }

    public function includeProfiles(ProfileAccounts $accounts)
    {
        return $this->collection($accounts->profiles, $this->profileTransformer);
    }

    public function includeAccounts(ProfileAccounts $accounts)
    {
        return $this->collection($accounts->accounts, $this->accountTransformer);
    }
}