<?php

namespace App\Repositories\Marketing\Facebook;

use App\Models\Marketing\Facebook\Marketplace;
use App\Repositories\Repository;
use Illuminate\Support\Collection;

interface ListingRepositoryInterface extends Repository
{
    /**
     * Get All Inventory Missing on Facebook
     *
     * @param Marketplace $integration
     * @param array $params
     * @return Collection<Listings>
     */
    public function getAllMissing(Marketplace $integration, array $params): Collection;

    /**
     * Get All Inventory Sold for Facebook Integration
     *
     * @param Marketplace $integration
     * @param array $params
     * @return Collection<Listings>
     */
    public function getAllSold(Marketplace $integration, array $params): Collection;
}