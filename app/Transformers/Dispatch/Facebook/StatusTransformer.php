<?php

namespace App\Transformers\Dispatch\Facebook;

use App\Services\Dispatch\Facebook\DTOs\MarketplaceStatus;
use App\Transformers\Dispatch\Facebook\DealerTransformer;
use App\Transformers\Dispatch\TunnelTransformer;
use League\Fractal\TransformerAbstract;

class StatusTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'dealers',
        'tunnels'
    ];

    /**
     * @var DealerTransformer
     */
    protected $dealerTransformer;

    /**
     * @var TunnelTransformer
     */
    protected $tunnelTransformer;

    public function __construct(
        DealerTransformer $dealerTransformer,
        TunnelTransformer $tunnelTransformer
    ) {
        $this->dealerTransformer = $dealerTransformer;
        $this->tunnelTransformer = $tunnelTransformer;
    }

    public function transform(MarketplaceStatus $status)
    {
        return [
            'config' => [
                'action' => $status->getAction(),
                'interval' => $status->getInterval(),
                'proxy' => $status->getProxyConfig(),
                'cookie' => $status->getCookieConfig(),
                'urls' => $status->getAllUrls(),
                'selectors' => $status->getAllSelectors()
            ]
        ];
    }

    public function includeDealers(MarketplaceStatus $status)
    {
        return $this->collection($status->dealers, $this->dealerTransformer);
    }

    public function includeTunnels(MarketplaceStatus $status)
    {
        return $this->collection($status->tunnels, $this->tunnelTransformer);
    }
}
