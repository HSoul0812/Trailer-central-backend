<?php

namespace App\Transformers\Integration\Google;

use App\Services\Integration\Google\DTOs\GoogleToken;
use League\Fractal\TransformerAbstract;

class GoogleTokenTransformer extends TransformerAbstract
{
    public function transform(GoogleToken $token)
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
