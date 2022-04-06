<?php

namespace App\Transformers\CRM\Leads\Facebook;

use App\Models\CRM\Leads\Facebook\User;
use League\Fractal\TransformerAbstract;

/**
 * Class UserTransformer
 * @package App\Transformers\CRM\Leads\Facebook
 */
class UserTransformer extends TransformerAbstract
{
    /**
     * @param User $user
     * @return array
     */
    public function transform(User $user): array
    {
        return  [
            'user_id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
