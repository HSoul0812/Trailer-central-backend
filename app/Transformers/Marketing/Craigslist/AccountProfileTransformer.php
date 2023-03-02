<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Services\Marketing\Craigslist\DTOs\Account;
use App\Transformers\User\UserTransformer;
use App\Transformers\Marketing\VirtualCardTransformer;
use App\Transformers\Marketing\Craigslist\ProfileTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class AccountTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class AccountProfileTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'profile'
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
     * 
     * @return array
     */
    public function transform(Account $account): array
    {
        return [
            'username' => $account->username,
        ];
    }

    public function includeProfile(Account $account)
    {
        return $this->collection($account->profiles, $this->profileTransformer);
    }
}