<?php

namespace App\DTO\Marketing;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class DealerTunnel
 * 
 * @package App\DTO\Marketing
 */
class DealerTunnel
{
    use WithConstructor, WithGetter;

    /**
     * @var int
     */
    private $dealerId;

    /**
     * @var int
     */
    private $port;

    /**
     * @var int
     */
    private $lastPing;


    /**
     * Get Tunnel Type From Config
     * 
     * @return string
     */
    public function getTunnelType(): string {
        return config('marketing.tunnels.type');
    }

    /**
     * Get Tunnel Host From Config
     * 
     * @return string
     */
    public function getTunnelHost(): string {
        return config('marketing.tunnels.host');
    }

    /**
     * Is Active?
     * 
     * @return bool
     */
    public function isActive(): bool {
        return $this->lastPing > (time() - (int) config('marketing.tunnels.max_ping'));
    }
}