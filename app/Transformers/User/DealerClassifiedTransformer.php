<?php

namespace App\Transformers\User;

use League\Fractal\TransformerAbstract;
use App\Models\User\User;

class DealerClassifiedTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
	return [
            'clsf_active' => $user->clsf_active
        ];
    }
}
