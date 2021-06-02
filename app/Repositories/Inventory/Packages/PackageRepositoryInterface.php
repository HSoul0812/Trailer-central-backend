<?php

namespace App\Repositories\Inventory\Packages;

use App\Repositories\Repository;

/**
 * Interface PackageInventoryInterface
 * @package App\Repositories\Inventory\Packages
 */
interface PackageRepositoryInterface extends Repository
{
    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;
}
