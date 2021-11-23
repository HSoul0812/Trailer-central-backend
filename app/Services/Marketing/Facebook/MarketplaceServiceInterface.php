<?php

namespace App\Services\Marketing\Facebook;

use App\Models\Integration\Facebook\Marketplace;

interface MarketplaceServiceInterface {
    /**
     * Create Marketplace
     * 
     * @param array $params
     * @return Marketplace
     */
    public function create(array $params): Marketplace;

    /**
     * Update Marketplace
     * 
     * @param array $params
     * @return Marketplace
     */
    public function update(array $params): Marketplace;

    /**
     * Delete Marketplace
     * 
     * @param int $id
     * @return boolean
     */
    public function delete(int $id): bool;
}