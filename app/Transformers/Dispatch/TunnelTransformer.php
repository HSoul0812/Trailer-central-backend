<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Transformers\Dispatch\TunnelsTransformer;
use League\Fractal\TransformerAbstract;

class StatusTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'tunnels'
    ];

    public function __construct(
        TunnelsTransformer $tunnelsTransformer
    ) {
        $this->tunnelsTransformer = $tunnelsTransformer;
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
            ]
        ];
    }

    public function includeTunnels(DealerFacebook $dealer)
    {
        return $this->collection($dealer->tunnels, $this->tunnelsTransformer);
    }
}