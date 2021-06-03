<?php

namespace App\Transformers\User;

use League\Fractal\TransformerAbstract;
use App\Models\User\User;

class NewsletterTransformer extends TransformerAbstract 
{
    public function transform(User $user)
    {                
	return [
            'newsletter_enabled' => $user->newsletter_enabled
        ];
    }
}
