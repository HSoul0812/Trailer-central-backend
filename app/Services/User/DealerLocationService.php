<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Contracts\LoggerServiceInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Feed\Mapping\Incoming\ApiEntityReference;
use App\Models\User\DealerLocation;
use App\Repositories\Feed\Mapping\Incoming\ApiEntityReferenceRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
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

    /** @var LoggerServiceInterface */
    private $loggerService;

    public function __construct(
        DealerLocationRepositoryInterface $locationRepo,
        InventoryRepositoryInterface $inventoryRepo,
        ApiEntityReferenceRepositoryInterface $apiReferenceRepo,
        LoggerServiceInterface $loggerService
    )
    {
        $this->locationRepo = $locationRepo;
        $this->inventoryRepo = $inventoryRepo;
        $this->apiEntityReferenceRepo = $apiReferenceRepo;
        $this->loggerService = $loggerService;
    }

    public function create(int $id, array $params): DealerLocation
    {
        throw new NotImplementedException;
    }

    public function update(int $id, array $params): bool
    {
        throw new NotImplementedException;
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
                    "records of DealerLocation{dealer_location_id={$location->dealer_location_id}}");
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
}
