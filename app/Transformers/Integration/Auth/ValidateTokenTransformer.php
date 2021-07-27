<?php

namespace App\Transformers\Integration\Auth;

use League\Fractal\TransformerAbstract;
use App\Services\Integration\Common\DTOs\ValidateToken;

class ValidateTokenTransformer extends TransformerAbstract
{
    public function transform(ValidateToken $token)
    {
        return [
            'is_valid' => $token->isValid,
            'is_expired' => $token->isExpired,
            'message' => $token->message
        ];
    }
}
