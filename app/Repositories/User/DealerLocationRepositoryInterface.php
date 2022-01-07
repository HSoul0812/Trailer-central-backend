<?php

namespace App\Repositories\User;

use App\Models\User\DealerLocation;
use App\Repositories\Repository;
use App\Repositories\TransactionalRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface DealerLocationRepositoryInterface extends Repository, TransactionalRepository
{
    public const DEFAULT_GET_PARAMS = [
        self::CONDITION_AND_WHERE => [
        ]
    ];

    /**
     * Find Dealer Location By Various Options
     *
     * @param array $params
     * @return Collection<DealerLocation>
     */
    public function find($params);

    /**
     * Get First Dealer SMS Number
     *
     * @param int $dealerId
     * @return type
     */
    public function findDealerSmsNumber($dealerId);

    /**
     * Get All Dealer SMS Numbers
     *
     * @param int $dealerId
     * @return type
     */
    public function findAllDealerSmsNumbers($dealerId);

    /**
     * Get Dealer Number for Location or Default
     *
     * @param int $dealerId
     * @param int $locationId
     * @return type
     */
    public function findDealerNumber($dealerId, $locationId);

    /**
     * @param array $params
     * @return DealerLocation
     * @throws InvalidArgumentException when provided "sales_tax_items" isn't an array
     * @throws InvalidArgumentException when provided "fees" isn't an array
     */
    public function create($params): DealerLocation;

    /**
     * @param array $params
     * @return LengthAwarePaginator
     */
    public function getAll($params): LengthAwarePaginator;

    /**
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Collection<DealerLocation>
     */
    public function findAll(array $params): \Illuminate\Database\Eloquent\Collection;

    public function dealerHasLocationWithId(int $dealerId, int $locationId): bool;

    /**
     * @param array $params
     * @return DealerLocation
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException when `dealer_location_id` has not been provided
     */
    public function get($params): DealerLocation;

    /**
     * @param int $dealerId
     */
    public function getDefaultByDealerId(int $dealerId): ?DealerLocation;

    public function turnOffDefaultLocationByDealerId(int $dealerId): bool;

    public function turnOffDefaultLocationForInvoicingByDealerId(int $dealerId): bool;

    /**
     * @param array $params
     * @throws InvalidArgumentException when `dealer_location_id` has not been provided
     * @return int number of touched rows
     */
    public function delete($params): int;

    /**
     * @param array $params
     * @throws InvalidArgumentException when `dealer_location_id` has not been provided
     */
    public function update($params): bool;

    /**
     * @param string $name
     * @param int $dealerId
     * @param int|null $dealerLocationId
     * @return bool true if exists
     */
    public function existByName(string $name, int $dealerId, ?int $dealerLocationId): bool;
}
