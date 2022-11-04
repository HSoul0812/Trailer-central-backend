<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Repositories\Repository;
use Illuminate\Support\Collection;

/**
 * Interface PosterRepositoryInterface
 * 
 * @package App\Repositories\Marketing\Craigslist
 */
interface PosterRepositoryInterface extends Repository {
    /**
     * Get All Tunnels For Dealer
     * 
     * @param array $params
     * @return Collection<DealerTunnel>
     */
    public function getByDealer(int $dealerId, string $server = self::SERVER_DEFAULT): Collection;
}