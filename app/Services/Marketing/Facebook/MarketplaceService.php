<?php

namespace App\Services\Marketing\Facebook;

use App\Models\Marketing\Facebook\Marketplace;
use App\Models\CRM\Text\NumberVerify;
use App\Repositories\Marketing\Facebook\MarketplaceRepositoryInterface;
use App\Repositories\Marketing\Facebook\FilterRepositoryInterface;
use App\Repositories\Marketing\Facebook\ErrorRepositoryInterface;
use App\Repositories\CRM\Text\VerifyRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\CRM\Text\TwilioServiceInterface;
use App\Services\Marketing\Facebook\DTOs\MarketplaceStatus;
use App\Services\Marketing\Facebook\DTOs\TfaType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
     * @var ErrorRepositoryInterface
     */
    protected $errors;

    /**
     * @var VerifyRepositoryInterface
     */
    protected $verifyNumber;

    /**
     * @var DealerLocationRepositoryInterface
     */
    protected $dealerLocation;

    /**
     * @var TwilioServiceInterface
     */
    protected $twilio;

    /**
     * Construct Facebook Marketplace Service
     *
     * @param MarketplaceRepositoryInterface $marketplace
     * @param FilterRepositoryInterface $filters
     * @param ErrorRepositoryInterface $errors
     * @param VerifyRepositoryInterface $verifyNumber
     * @param DealerLocationRepositoryInterface $dealerLocation
     * @param TwilioServiceInterface $twilio
     */
    public function __construct(
        MarketplaceRepositoryInterface $marketplace,
        FilterRepositoryInterface $filters,
        ErrorRepositoryInterface $errors,
        VerifyRepositoryInterface $verifyNumber,
        DealerLocationRepositoryInterface $dealerLocation,
        TwilioServiceInterface $twilio
    ) {
        $this->marketplace = $marketplace;
        $this->filters = $filters;
        $this->errors = $errors;
        $this->verifyNumber = $verifyNumber;
        $this->dealerLocation = $dealerLocation;
        $this->twilio = $twilio;

        // Create Marketplace Logger
        $this->log = Log::channel('marketplace');
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
        } catch (\Exception $e) {
            $this->log->error('Marketplace Integration update error. params=' .
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
        } catch (\Exception $e) {
            $this->log->error('Marketplace Integration update error. params=' .
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
        } catch (\Exception $e) {
            $this->log->error('Marketplace Integration update error. params=' .
                json_encode(['id' => $id]), $e->getTrace());

            $this->marketplace->rollbackTransaction();

            throw $e;
        }
    }

    /**
     * Get Two-Factor Auth Types Array
     *
     * @param int $dealerId
     * @return MarketplaceStatus
     */
    public function status(int $dealerId): MarketplaceStatus {
        // Initialize Collection
        $tfaTypes = new Collection();

        // Loop Through TFA Types
        $types = explode(",", config('marketing.fb.settings.fields.tfa_types', ''));
        foreach(Marketplace::TFA_TYPES_ACTIVE as $code) {
            // Skip If Not in Active TFA Types
            if(!in_array($code, $types)) {
                continue;
            }

            // Get Autocomplete
            $autocomplete = null;
            if($code === TfaType::TYPE_SMS) {
                $autocomplete = $this->dealerLocation->findAllDealerSmsNumbers($dealerId);
            }

            // Append TFA Types
            $tfaTypes->push(new TfaType([
                'code' => $code,
                'name' => Marketplace::TFA_TYPES[$code],
                'autocomplete' => $autocomplete
            ]));
        }

        // Return MarketplaceStatus
        return new MarketplaceStatus([
            'page_url' => config('marketing.fb.settings.fields.page_url', false),
            'errors' => $this->errors->getAllActive($dealerId),
            'tfa_types' => new Collection($tfaTypes)
        ]);
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
        $verify = $this->verifyNumber->get($dealerNo, $type);

        // No Existing Number
        if(empty($verify->id)) {
            // Get New Verification Number
            $verify = $this->twilio->getVerifyNumber($dealerNo, $type);
        }

        // Return Response
        return $verify;
    }
}
