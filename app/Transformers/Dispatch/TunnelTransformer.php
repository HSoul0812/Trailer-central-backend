<?php

namespace App\Transformers\Dispatch\Facebook;

use App\DTO\Marketing\DealerTunnel;
use League\Fractal\TransformerAbstract;

class StatusTransformer extends TransformerAbstract
{
    public function transform(DealerTunnel $tunnel)
    {
        return [
            'dealer' => $tunnel->dealerId,
            'type' => $tunnel->getTunnelType(),
            'host' => $tunnel->getTunnelHost(),
            'port' => $tunnel->port,
            'is_active' => $tunnel->isActive()
        ];
    }
}