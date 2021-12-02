<?php

namespace App\Services\Marketing\Facebook;

use App\Models\Marketing\Facebook\Marketplace;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\FilterRepositoryInterface;
use App\Http\Requests\Marketing\Facebook\CreateMarketplaceRequest;
use App\Http\Requests\Marketing\Facebook\UpdateMarketplaceRequest;

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
     * @param CreateMarketplaceRequest $request
     * @return Marketplace
     */
    public function create(CreateMarketplaceRequest $request): Marketplace {
        // Begin Transaction
        $this->marketplace->beginTransaction();

        try {
            // Create Marketplace Integration
            $marketplace = $this->marketplace->create($request->all());

            // Create All Filters
            if($request->filters && is_array($request->filters)) {
                foreach($request->filters as $filter) {
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
            $this->logger->error('Marketplace Integration creation error. params=' .
                json_encode($request->all() + ['dealer_id' => $request->dealer_id]),
                $e->getTrace());

            $this->marketplace->rollbackTransaction();

            throw $e;
        }
    }

    /**
     * Update Marketplace
     * 
     * @param UpdateMarketplaceRequest $request
     * @return Marketplace
     */
    public function update(UpdateMarketplaceRequest $request): Marketplace {
        // Begin Transaction
        $this->marketplace->beginTransaction();

        try {
            // Update Marketplace Integration
            $marketplace = $this->marketplace->update($request->all());

            // Delete Existing Filters
            $this->filters->deleteAll($marketplace->id);

            // Create All Filters
            if($request->filters && is_array($request->filters)) {
                foreach($request->filters as $filter) {
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
                json_encode($request->all() + ['marketplace_id' => $request->id]),
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
                json_encode($request->all() + ['marketplace_id' => $request->id]),
                $e->getTrace());

            $this->marketplace->rollbackTransaction();

            throw $e;
        }
    }
}
