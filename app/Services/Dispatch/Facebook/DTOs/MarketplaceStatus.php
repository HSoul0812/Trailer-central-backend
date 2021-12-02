<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\Services\Dispatch\Facebook\DTOs\DealerFacebook;
use App\Services\Dispatch\Facebook\DTOs\DealerTunnel;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class CommonToken
 * 
 * @package App\Services\Dispatch\Facebook\DTOs
 */
class MarketplaceStatus
{
    use WithConstructor, WithGetter;

    /**
     * @var Collection<DealerFacebook>
     */
    private $dealers;

    /**
     * @var Collection<DealerTunnel>
     */
    private $tunnels;
}