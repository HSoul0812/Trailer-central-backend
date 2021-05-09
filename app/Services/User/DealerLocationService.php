<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Contracts\LoggerServiceInterface;
use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Models\User\DealerLocation;
use App\Repositories\Feed\Mapping\Incoming\ApiEntityReferenceRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\DealerLocationQuoteFeeRepository;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Repositories\User\DealerLocationSalesTaxItemRepositoryInterface;
use App\Repositories\User\DealerLocationSalesTaxRepositoryInterface;
use InvalidArgumentException;
use DomainException;
use Exception;

class DealerLocationService implements DealerLocationServiceInterface
{
    /** @var DealerLocationRepositoryInterface */
    private $locationRepo;

    /** @var InventoryRepositoryInterface */
    private $inventoryRepo;

    /** @var ApiEntityReferenceRepositoryInterface */
    private $apiEntityReferenceRepo;

    /** @var DealerLocationSalesTaxRepositoryInterface */
    private $salesTaxRepo;

    /** @var DealerLocationSalesTaxItemRepositoryInterface */
    private $salesTaxItemRepo;

    /** @var DealerLocationQuoteFeeRepository */
    private $quoteFeeRepo;

    /** @var LoggerServiceInterface */
    private $loggerService;

    public function __construct(
        DealerLocationRepositoryInterface $locationRepo,
        InventoryRepositoryInterface $inventoryRepo,
        ApiEntityReferenceRepositoryInterface $apiReferenceRepo,
        DealerLocationSalesTaxRepositoryInterface $salesTaxRepo,
        DealerLocationSalesTaxItemRepositoryInterface $salesTaxItemRepo,
        DealerLocationQuoteFeeRepository $quoteFeeRepo,
        LoggerServiceInterface $loggerService
    )
    {
        $this->locationRepo = $locationRepo;
        $this->inventoryRepo = $inventoryRepo;
        $this->apiEntityReferenceRepo = $apiReferenceRepo;
        $this->salesTaxRepo = $salesTaxRepo;
        $this->salesTaxItemRepo = $salesTaxItemRepo;
        $this->quoteFeeRepo = $quoteFeeRepo;
        $this->loggerService = $loggerService;
    }

    /**
     * @throws Exception when there was some unknown db error
     * @throws InvalidArgumentException when provided "sales_tax_items" isn't an array
     * @throws InvalidArgumentException when provided "fees" isn't an array
     */
    public function create(int $dealerId, array $params): DealerLocation
    {
        try {
            $this->locationRepo->beginTransaction();

            if (!empty($params['is_default_for_invoice'])) {
                // remove any default location for invoice if exists
                $this->locationRepo->turnOffDefaultLocationForInvoiceByDealerId($dealerId);
            }

            $salesTaxItemColumnTitles = $this->encodeTaxColumnTitles($params['sales_tax_item_column_titles'] ?? []);

            $location = $this->locationRepo->create(
                $params + ['sales_tax_item_column_titles' => $salesTaxItemColumnTitles, 'dealer_id' => $dealerId]
            );

            $locationRelDefinition = ['dealer_location_id' => $location->dealer_location_id];

            $this->salesTaxRepo->create($params + $locationRelDefinition);

            if (!empty($params['sales_tax_items'])) {
                if (!is_array($params['sales_tax_items'])) {
                    throw new InvalidArgumentException('"sales_tax_items" must be an array');
                }

                foreach ($params['sales_tax_items'] as $item) {
                    $this->salesTaxItemRepo->create($item + $locationRelDefinition);
                    $this->salesTaxItemRepo->createV1($item + $locationRelDefinition);// for backward compatibility
                }
            }

            if (!empty($params['fees'])) {
                if (!is_array($params['fees'])) {
                    throw new InvalidArgumentException('"fees" must be an array');
                }

                foreach ($params['fees'] as $item) {
                    $this->quoteFeeRepo->create($item + $locationRelDefinition);
                }
            }

            $this->locationRepo->commitTransaction();

            return $location;
        } catch (Exception $e) {
            $this->loggerService->error(
                'Dealer location creation error. params=' .
                json_encode($params + ['dealer_id' => $dealerId]),
                $e->getTrace());

            $this->locationRepo->rollbackTransaction();

            throw $e;
        }
    }

    /**
     * @param int $locationId
     * @param int $dealerId
     * @param array $params
     *
     * @return bool
     *
     * @throws Exception when there was some unknown db error
     * @throws InvalidArgumentException when `dealer_id` has not been provided
     * @throws InvalidArgumentException when `dealer_location_id` has not been provided
     * @throws InvalidArgumentException when `sales_tax_items` is not an array
     * @throws InvalidArgumentException when `fees` is not an array
     */
    public function update(int $locationId, int $dealerId, array $params): bool
    {
        try {
            $this->locationRepo->beginTransaction();

            if (!empty($params['is_default_for_invoice'])) {
                // remove any default location for invoice if exists
                $this->locationRepo->turnOffDefaultLocationForInvoiceByDealerId($dealerId);
            }

            $locationRelDefinition = ['dealer_location_id' => $locationId];

            $salesTaxItemColumnTitles = $this->encodeTaxColumnTitles($params['sales_tax_item_column_titles'] ?? []);

            $this->locationRepo->update(
                $params + $locationRelDefinition + ['sales_tax_item_column_titles' => $salesTaxItemColumnTitles]
            );

            $this->salesTaxRepo->updateOrCreateByDealerLocationId($locationId, $params);

            if (!empty($params['sales_tax_items'])) {
                if (!is_array($params['sales_tax_items'])) {
                    throw new InvalidArgumentException('"sales_tax_items" must be an array');
                }

                $this->salesTaxItemRepo->deleteByDealerLocationId($locationId);
                $this->salesTaxItemRepo->deleteByDealerLocationIdV1($locationId);// for backward compatibility

                foreach ($params['sales_tax_items'] as $item) {
                    $this->salesTaxItemRepo->create($item + $locationRelDefinition);
                    $this->salesTaxItemRepo->createV1($item + $locationRelDefinition);// for backward compatibility
                }
            }

            if (!empty($params['fees'])) {
                if (!is_array($params['fees'])) {
                    throw new InvalidArgumentException('"fees" must be an array');
                }

                $this->quoteFeeRepo->deleteByDealerLocationId($locationId);

                foreach ($params['fees'] as $item) {
                    $this->quoteFeeRepo->create($item + $locationRelDefinition);
                }
            }

            $this->locationRepo->commitTransaction();

            return true;
        } catch (Exception $e) {
            $this->loggerService->error(
                'Dealer location updating error. params=' .
                json_encode($params + ['dealer_location_id' => $locationId]),
                $e->getTrace());

            $this->locationRepo->rollbackTransaction();

            throw $e;
        }
    }

    /**
     * @throws Exception when there was some unknown db error
     * @throws DomainException when there wasn't a possible location to move those related related records
     */
    public function moveAndDelete(int $id, ?int $moveToLocationId = null): bool
    {
        $location = $this->locationRepo->get(['dealer_location_id' => $id]);

        try {
            $this->locationRepo->beginTransaction();

            if ($location->hasRelatedRecords()) {
                $this->moveRelatedRecords($location, $moveToLocationId);
            }

            $result = (bool)$this->locationRepo->delete(['dealer_location_id' => $id]);

            $this->locationRepo->commitTransaction();

            return $result;
        } catch (Exception $e) {
            $this->loggerService->error(
                'Dealer location deletion error. params=' .
                json_encode(['id' => $id, 'moveToLocationId' => $moveToLocationId]),
                $e->getTrace());

            $this->locationRepo->rollbackTransaction();

            throw $e;
        }
    }

    /**
     * @throws Exception when there was some unknown db error
     * @throws DomainException when there wasn't a possible location to move those related related records
     */
    public function moveRelatedRecords(DealerLocation $location, ?int $moveToLocationId = null): bool
    {
        try {
            $this->locationRepo->beginTransaction();

            $moveToLocationId = $moveToLocationId ??
                $this->getAnotherAvailableLocationIdToMove($location->dealer_location_id, $location->dealer_id);

            if ($moveToLocationId === null) {
                throw new DomainException("There isn't a possible location to move those related " .
                    "records of DealerLocation{dealer_location_id=$location->dealer_location_id}");
            }

            $this->inventoryRepo->moveLocationId($location->dealer_location_id, $moveToLocationId);
            $this->apiEntityReferenceRepo->updateMultiples(
                [
                    'entity_id' => $location->dealer_location_id,
                    'entity_type' => ApiEntityReference::TYPE_LOCATION
                ],
                [
                    'entity_id' => $moveToLocationId
                ]
            );

            $this->locationRepo->commitTransaction();
        } catch (Exception $e) {
            $this->loggerService->error(
                'Dealer location moving error. params=' .
                json_encode(['dealer_location_id' => $location->dealer_location_id, 'moveToLocationId' => $moveToLocationId]),
                $e->getTrace()
            );

            $this->locationRepo->rollbackTransaction();

            throw $e;
        }

        return true;
    }

    public function getAnotherAvailableLocationIdToMove(int $locationId, int $dealerId): ?int
    {
        $default = $this->locationRepo->getDefaultByDealerId($dealerId);

        // if there is a default dealer location, then it'll assign it, otherwise it'll try to assign the first location
        if ($default && $default->dealer_location_id !== $locationId) {
            return $default->dealer_location_id;
        }

        /** @var DealerLocation $first */

        $whereFirst = [['dealer_location_id', '!=', $locationId]];

        $first = $this->locationRepo->findAll([
            'dealer_id' => $dealerId,
            DealerLocationRepositoryInterface::CONDITION_AND_WHERE => $whereFirst
        ])->first();

        return $first->dealer_location_id ?: null;
    }

    /**
     * Forces a value to be an array, if it is a json it will be encoded as array
     *
     * @param array|string $titles
     * @return array
     */
    private function encodeTaxColumnTitles($titles): array
    {
        $salesTaxItemColumnTitles = [];

        if (!empty($titles)) {
            $salesTaxItemColumnTitles = $titles;

            if (is_string($titles)) {
                $salesTaxItemColumnTitles = json_decode($titles, true);
            }
        }

        return $salesTaxItemColumnTitles;
    }
}
