<?php

namespace App\Transformers\Integration\Auth;

use App\Services\Integration\Common\DTOs\CommonToken;
use League\Fractal\TransformerAbstract;

class CommonTokenTransformer extends TransformerAbstract
{
    public function transform(CommonToken $token)
    {
        return [
            'access_token' => $token->getAccessToken(),
            'refresh_token' => $token->getRefreshToken(),
            'id_token' => $token->getIdToken(),
            'scopes' => $token->getScope(),
            'issued_at' => $token->getIssuedAt(),
            'expires_in' => $token->getExpiresIn(),
            'expires_at' => $token->getExpiresAt(),
            'issued_at' => $token->getIssuedAt()
        ];
    }
}
