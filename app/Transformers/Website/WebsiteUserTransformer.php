<?php

namespace App\Transformers\Website;

use App\Models\Website\User\WebsiteUser;
use App\Models\Website\Website;
use League\Fractal\TransformerAbstract;

/**
 * Class WebsiteTransformer
 * @package App\Transformers\Website
 */
class WebsiteUserTransformer extends TransformerAbstract
{
    public function transform(WebsiteUser $user): array
    {
        return [
            'access_token' => $user->token->access_token,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'website_id' => $user->website_id,
            ]
        ];
    }
}
