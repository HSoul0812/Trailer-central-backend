<?php

declare(strict_types=1);

namespace App\Repositories\Inventory\CustomOverlay;

use App\Models\Inventory\CustomOverlay;
use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

interface CustomOverlayRepositoryInterface extends Repository
{
    /**
     * Creates the record
     *
     * @param array $params
     * @return CustomOverlay
     */
    public function create($params): CustomOverlay;

    /**
     * Updates the record
     *
     * @param array $params
     * @return bool
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update($params): bool;

    /**
     * Updates the record
     *
     * @param array $params
     * @return bool
     */
    public function upsert(array $params): bool;

    /**
     * Retrieves the record
     *
     * @param array $params
     */
    public function get($params): ?CustomOverlay;

    /**
     * Deletes the record
     *
     * @param array $params
     * @return bool
     */
    public function delete($params): bool;

    /**
     * Gets all records by $params
     *
     * @param array $params
     * @return  array<CustomOverlay>|Collection
     */
    public function getAll($params): Collection;
}
