<?php

namespace App\Repositories\Marketing\Craigslist;

use App\Repositories\Repository;
use App\Services\Marketing\Craigslist\DTOs\Client;
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

    /**
     * Email Last Sent in Last Interval
     * 
     * @param string $email
     * @param int $interval
     * @return int
     */
    public function sentIn(string $email, int $interval): int;

    /**
     * Email Last Sent in Last Interval
     * 
     * @param string $email
     * @return void
     */
    public function markSent(string $email): void;
}