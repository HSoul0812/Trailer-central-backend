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
     * Was the Email Last Sent Within the Interval?
     * 
     * @param string $email
     * @param int $interval
     * @return int
     */
    public function sentIn(string $email, int $interval): int;

    /**
     * Mark Email's Last Sent Time to Now
     * 
     * @param string $email
     * @return void
     */
    public function markSent(string $email): void;
}