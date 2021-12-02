<?php

namespace App\Services\Marketing\Facebook;

use App\Models\Marketing\Facebook\Marketplace;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\FilterRepositoryInterface;

/**
 * Class MarketplaceService
 * 
 * @package App\Services\Marketing\Facebook
 */
class MarketplaceService implements MarketplaceServiceInterface
{
    /**
     * @var MarketplaceRepositoryInterface
     */
    protected $marketplace;

    /**
     * @var FilterRepositoryInterface
     */
    protected $filters;

    /**
     * Construct Facebook Marketplace Service
     * 
     * @param MarketplaceRepositoryInterface $marketplace
     * @param FilterRepositoryInterface $filters
     */
    public function __construct(
        MarketplaceRepositoryInterface $marketplace,
        FilterRepositoryInterface $filters
    ) {
        $this->marketplace = $marketplace;
        $this->filters = $filters;
    }

    /**
     * Create Marketplace
     * 
     * @param array $params
     * @return Marketplace
     */
    public function create(array $params): Marketplace {
        // Begin Transaction
        $this->marketplace->beginTransaction();

        try {
            // Create Marketplace Integration
            $marketplace = $this->marketplace->create($params);

            // Create All Filters
            if($params['filters'] && is_array($params['filters'])) {
                foreach($params['filters'] as $filter) {
                    $this->filters->create([
                        'marketplace_id' => $marketplace->id,
                        'filter_type' => $filter['type'],
                        'filter' => $filter['value']
                    ]);
                }
            }

            $this->marketplace->commitTransaction();

            // Return Response
            return $marketplace;
        } catch (Exception $e) {
            $this->logger->error('Marketplace Integration update error. params=' .
                json_encode($params + ['marketplace_id' => $params['id']]),
                $e->getTrace());

            $this->marketplace->rollbackTransaction();

            throw $e;
        }
    }

    /**
     * Update Marketplace
     * 
     * @param array $params
     * @return Marketplace
     */
    public function update(array $params): Marketplace {
        // Begin Transaction
        $this->marketplace->beginTransaction();

        try {
            // Update Marketplace Integration
            $marketplace = $this->marketplace->update($params);

            // Delete Existing Filters
            $this->filters->deleteAll($marketplace->id);

            // Create All Filters
            if($params['filters'] && is_array($params['filters'])) {
                foreach($params['filters'] as $filter) {
                    $this->filters->create([
                        'marketplace_id' => $marketplace->id,
                        'filter_type' => $filter['type'],
                        'filter' => $filter['value']
                    ]);
                }
            }

            $this->marketplace->commitTransaction();

            // Return Response
            return $marketplace;
        } catch (Exception $e) {
            $this->logger->error('Marketplace Integration update error. params=' .
                json_encode($params + ['marketplace_id' => $params['id']]),
                $e->getTrace());

            $this->marketplace->rollbackTransaction();

            throw $e;
        }
    }

    /**
     * Delete Marketplace
     * 
     * @param int $id
     * @return boolean
     */
    public function delete(int $id): bool {
        // Begin Transaction
        $this->marketplace->beginTransaction();

        try {
            // Delete Filters for Marketplace Integration
            $this->filters->deleteAll($id);

            // Delete Marketplace
            $success = $this->marketplace->delete($id);

            $this->marketplace->commitTransaction();

            // Return Result
            return $success;
        } catch (Exception $e) {
            $this->logger->error('Marketplace Integration update error. params=' .
                json_encode(['id' => $id]), $e->getTrace());

            $this->marketplace->rollbackTransaction();

            throw $e;
        }
    }
}
