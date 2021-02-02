<?php

namespace App\Transformers\Integration\Auth;

use App\Services\Integration\Common\DTOs\EmailToken;
use League\Fractal\TransformerAbstract;

class EmailTokenTransformer extends TransformerAbstract
{
    public function transform(EmailToken $token)
    {
        return [
            'access_token' => $token->getAccessToken(),
            'refresh_token' => $token->getRefreshToken(),
            'id_token' => $token->getIdToken(),
            'scopes' => $token->getScope(),
            'issued_at' => $token->getIssuedAt(),
            'expires_in' => $token->getExpiresIn(),
            'expires_at' => $token->getExpiresAt(),
            'issued_at' => $token->getIssuedAt(),
            'email' => $token->getEmailAddress()
        ];
    }
}
