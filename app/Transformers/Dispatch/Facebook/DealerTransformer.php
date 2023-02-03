<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Transformers\Dispatch\Facebook\PaginatorTransformer;
use App\Transformers\Dispatch\TunnelTransformer;
use League\Fractal\TransformerAbstract;

class DealerTransformer extends TransformerAbstract
{
    /**
     * @var TunnelTransformer
     */
    private $tunnelTransformer;

    /**
     * @var PaginatorTransformer
     */
    private $paginatorTransformer;


    protected $defaultIncludes = [
        'tunnels',
        'missing',
        'sold'
    ];

    public function __construct(
        TunnelTransformer $tunnelTransformer,
        PaginatorTransformer $paginatorTransformer
    ) {
        $this->tunnelTransformer = $tunnelTransformer;
        $this->paginatorTransformer = $paginatorTransformer;
    }

    public function transform(DealerFacebook $dealer)
    {
        return [
            'id' => $dealer->dealerId,
            'locationId' => $dealer->dealerLocationId,
            'name' => $dealer->dealerName,
            'integration' => $dealer->integrationId,
            'posts_per_day' => $dealer->posts_per_day,
            'fb' => [
                'username' => $dealer->fbUsername,
                'password' => $dealer->fbPassword
            ],
            'auth' => [
                'type' => $dealer->authType,
                'username' => $dealer->authUsername,
                'password' => $dealer->authPassword,
                'code' => $dealer->authCode
            ],
            'last_attempt_ts' => $dealer->last_attempt_ts
        ];
    }

    public function includeTunnels(DealerFacebook $dealer)
    {
        return $this->collection($dealer->tunnels, $this->tunnelTransformer);
    }

    public function includeMissing(DealerFacebook $dealer)
    {
        if ($dealer->missing) {
            return $this->item($dealer->missing, $this->paginatorTransformer);
        }
        return $this->null();
    }
    
    public function includeSold(DealerFacebook $dealer)
    {
        if ($dealer->sold) {
            return $this->item($dealer->sold, $this->paginatorTransformer);
        }
        return $this->null();
    }
}
