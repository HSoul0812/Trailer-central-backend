<?php

namespace App\Transformers\CRM\User;

use App\Services\CRM\User\DTOs\AuthType;
use League\Fractal\TransformerAbstract;

class AuthTypeTransformer extends TransformerAbstract
{
    /**
     * Transform AuthType
     * 
     * @param AuthType $config
     * @return array
     */
    public function transform(AuthType $config): array
    {
        return [
            'index' => $config->index,
            'label' => $config->label,
            'method' => $config->method,
            'auth' => $config->auth
        ];
    }
}

