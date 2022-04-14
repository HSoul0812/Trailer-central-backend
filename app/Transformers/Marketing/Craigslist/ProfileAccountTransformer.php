<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Profile;
use App\Transformers\Marketing\Craigslist\ProfileTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class ProfileAccountTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class ProfileAccountTransformer extends TransformerAbstract
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
     * @param Profile $profile
     * @return array
     */
    public function transform(ProfileAccounts $account): array
    {
        return [
            'username' => $account->username
        ];
    }

    public function includeProfiles(ProfileAccounts $account)
    {
        return $this->collection($account->profiles, $this->profileTransformer);
    }
}