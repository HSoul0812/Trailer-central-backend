<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Services\Marketing\Craigslist\DTOs\Account;
use App\Transformers\Marketing\Craigslist\ProfileTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class AccountTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class AccountTransformer extends TransformerAbstract
{ 
    protected $defaultIncludes = [
        'profiles'
    ];

    /**
     * @var ProfileTransformer
     */
    protected $profileTransformer;

    public function __construct(
        ProfileTransformer $profileTransformer
    ) {
        $this->profileTransformer = $profileTransformer;
    }

    /**
     * @param Account $account
     * @return array
     */
    public function transform(Account $account): array
    {
        return [
            'username' => $account->username
        ];
    }

    public function includeProfiles(Account $account)
    {
        return $this->collection($account->profiles, $this->profileTransformer);
    }
}