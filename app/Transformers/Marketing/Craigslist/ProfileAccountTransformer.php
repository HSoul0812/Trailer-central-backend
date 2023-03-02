<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Services\Marketing\Craigslist\DTOs\Account;
use App\Services\Marketing\Craigslist\DTOs\ProfileAccounts;
use App\Transformers\Marketing\Craigslist\ProfileTransformer;
use App\Transformers\Marketing\Craigslist\AccountTransformer;
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
     * @var AccountTransformer
     */
    protected $accountTransformer;

    public function __construct(
        ProfileTransformer $profileTransformer,
        AccountTransformer $accountTransformer
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
        return $this->collection($accounts->accounts, function(Account $account) {
            var_dump($account->profiles);
            return [
                'username' => $account->username,
                'profiles' => $this->collection($account->profiles, $this->profileTransformer)
            ];
        });
    }
}