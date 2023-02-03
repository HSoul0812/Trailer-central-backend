<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\DTO\Marketing\DealerTunnel;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class DealerFacebook
 *
 * @package App\Services\Dispatch\Facebook\DTOs
 */
class DealerFacebook
{
    use WithConstructor, WithGetter;

    /**
     * @var int
     */
    private $dealerId;

    /**
     * @var int
     */
    private $dealerLocationId;

    /**
     * @var string
     */
    private $dealerName;

    /**
     * @var int
     */
    private $integrationId;

    /**
     * @var string
     */
    private $fbUsername;

    /**
     * @var string
     */
    private $fbPassword;

    /**
     * @var string
     */
    private $authUsername;

    /**
     * @var string
     */
    private $authPassword;

    /**
     * @var string
     */
    private $authCode;

    /**
     * @var string
     */
    private $authType;

    /**
     * @var Collection<DealerTunnel>
     */
    private $tunnels;

    /**
     * @var Collection<InventoryFacebook>
     */
    private $missing;

    /**
     * @var Collection<InventoryFacebook>
     */
    private $sold;

    /**
     * @var $string
     */
    private $last_attempt_ts;

    /**
     * @var int
     */
    private $posts_per_day;
}
