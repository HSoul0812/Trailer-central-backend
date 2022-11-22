<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Repositories\Repository;
use App\Service\Marketing\Craigslist\DTOs\Client;
use Illuminate\Support\Collection;

/**
 * Interface ClientRepositoryInterface
 * 
 * @package App\Repositories\Marketing\Craigslist
 */
interface ClientRepositoryInterface extends Repository {
    /**
     * Get All Internal Client
     * 
     * @param array $params
     * @return Collection<Client>
     */
    public function getAllInternal(): Collection;
}