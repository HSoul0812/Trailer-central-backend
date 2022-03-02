<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Profile;
use League\Fractal\TransformerAbstract;

/**
 * Class ProfileTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class ProfileTransformer extends TransformerAbstract
{
    /**
     * @param Profile $profile
     * @return array
     */
    public function transform(Profile $profile): array
    {
        return [
            'id' => $profile->id,
            'profile' => $profile->profile,
            'username' => $profile->username,
            'category' => $profile->category
        ];
    }
}