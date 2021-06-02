<?php

namespace App\Services\Inventory\Packages;

use App\Models\Inventory\Packages\Package;

/**
 * Interface PackageServiceInterface
 * @package App\Services\Inventory\Packages
 */
interface PackageServiceInterface
{
    /**
     * @param array $params
     * @return Package|null
     */
    public function create(array $params): ?Package;

    /**
     * @param int $id
     * @param array $params
     * @return Package|null
     */
    public function update(int $id, array $params): ?Package;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
