<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Transformers\Dispatch\TunnelTransformer;
use League\Fractal\TransformerAbstract;

class DealerTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'tunnels'
    ];

    public function __construct(TunnelTransformer $tunnelTransformer) {
        $this->tunnelTransformer = $tunnelTransformer;
    }

    public function transform(DealerFacebook $dealer)
    {
        return [
            'id' => $dealer->dealerId,
            'name' => $dealer->dealerName,
            'integration' => $dealer->integrationId,
            'fb' => [
                'username' => $dealer->fbUsername,
                'password' => $dealer->fbPassword
            ],
            'auth' => [
                'type' => $dealer->authType,
                'username' => $dealer->authUsername,
                'password' => $dealer->authPassword
            ],
            'inventory' => $dealer->inventory
        ];
    }

    public function includeTunnels(DealerFacebook $dealer)
    {
        return $this->collection($dealer->tunnels, $this->tunnelTransformer);
    }
}