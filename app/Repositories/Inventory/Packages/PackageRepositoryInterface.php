<?php

namespace App\Repositories\Inventory\Packages;

use App\Repositories\Repository;
use App\Repositories\TransactionalRepository;

/**
 * Interface PackageInventoryInterface
 * @package App\Repositories\Inventory\Packages
 */
interface PackageRepositoryInterface extends Repository, TransactionalRepository
{
}
