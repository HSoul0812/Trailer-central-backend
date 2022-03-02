<?php

namespace App\Services\Marketing\Facebook;

use App\Models\Marketing\Facebook\Marketplace;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\FilterRepositoryInterface;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\CRM\Text\TextServiceInterface;

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
     * @var NumberRepositoryInterface
     */
    protected $textNumber;

    /**
     * @var DealerLocationRepositoryInterface
     */
    protected $dealerLocation;

    /**
     * @var TextServiceInterface
     */
    protected $twilio;

    /**
     * Construct Facebook Marketplace Service
     * 
     * @param MarketplaceRepositoryInterface $marketplace
     * @param FilterRepositoryInterface $filters
     * @param NumberRepositoryInterface $textNumber
     * @param DealerLocationRepositoryInterface $dealerLocation
     * @param TextServiceInterface $twilio
     */
    public function __construct(
        MarketplaceRepositoryInterface $marketplace,
        FilterRepositoryInterface $filters,
        NumberRepositoryInterface $textNumber,
        DealerLocationRepositoryInterface $dealerLocation,
        TextServiceInterface $twilio
    ) {
        $this->marketplace = $marketplace;
        $this->filters = $filters;
        $this->textNumber = $textNumber;
        $this->dealerLocation = $dealerLocation;
        $this->twilio = $twilio;
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
            if(isset($params['filters']) && is_array($params['filters'])) {
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

    /**
     * Get Two-Factor Auth Types Array
     * 
     * @param int $dealerId
     * @return Collection<TfaType>
     */
    public function tfa(int $dealerId): Collection {
        // Initialize Collection
        $tfaTypes = new Collection();

        // Loop Through TFA Types
        foreach(Marketplace::TFA_TYPES as $code => $name) {
            // Get Autocomplete
            $autocomplete = null;
            if($code === TfaType::TFA_SMS) {
                $autocomplete = $this->dealerLocation->findAllDealerSmsNumbers($dealerId);
            }

            // Append TFA Types
            $tfaTypes->push(new TfaType([
                'code' => $code,
                'name' => $name,
                'autocomplete' => $autocomplete
            ]));
        }

        // Return Collection<TfaType>
        return $tfaTypes;
    }

    /**
     * Get Two-Factor Twilio Number for Marketplace
     * 
     * @param string $dealerNo
     * @param null|string $type
     * @return NumberVerify
     */
    public function sms(string $dealerNo, ?string $type = null): NumberVerify {
        // Get Existing Verify Number?
        $verify = $this->textNumber->getVerifyNumber($dealerNo, $type);

        // No Existing Number
        if(empty($verify->id)) {
            // Get New Verification Number
            $verify = $this->twilio->getVerifyNumber($dealerNo, $type);
        }

        // Return Response
        return $verify;
    }
}
