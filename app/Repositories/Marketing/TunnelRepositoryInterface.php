<?php

namespace App\Repositories\Marketing;

use App\Repositories\Repository;
use Illuminate\Support\Collection;

/**
 * Interface TunnelRepositoryInterface
 * 
 * @package App\Repositories\Marketing
 */
interface TunnelRepositoryInterface extends Repository {
    /**
     * @const Default Server
     */
    const SERVER_DEFAULT = 'prod';


    /**
     * Get All Tunnels For Dealer
     * 
     * @param array $params
     * @return Collection<DealerTunnel>
     */
    public function getByDealer(int $dealerId, string $server = self::SERVER_DEFAULT): Collection;
}
