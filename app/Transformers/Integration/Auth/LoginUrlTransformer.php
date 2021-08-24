<?php

namespace App\Transformers\Integration\Auth;

use App\Services\Integration\Common\DTOs\LoginUrlToken;
use League\Fractal\TransformerAbstract;

class LoginUrlTransformer extends TransformerAbstract
{
    public function transform(LoginUrlToken $login): array
    {
        return [
            'url' => $login->loginUrl,
            'state' => $login->authState
        ];
    }
}
