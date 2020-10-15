<?php

namespace App\Transformers\Integration\Auth;

use League\Fractal\TransformerAbstract;
use App\Models\Integration\Auth\AccessToken;

class TokenTransformer extends TransformerAbstract
{
    public function transform(AccessToken $token)
    {
        return [
            'id' => $token->id,
            'dealer_id' => $token->dealer_id,
            'token_type' => $token->token_type,
            'relation_type' => $token->relation_type,
            'relation_id' => $token->relation_id,
            'access_token' => $token->access_token,
            'id_token' => $token->id_token,
            'scope' => $token->scope,
            'issued_at' => $token->issued_at,
            'expires_in' => $token->expires_in,
            'expires_at' => $token->expires_at,
            'created_at' => $token->created_at,
            'updated_at' => $token->updated_at
        ];
    }
}
