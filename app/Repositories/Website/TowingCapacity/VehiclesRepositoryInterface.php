<?php


namespace App\Repositories\Website\TowingCapacity;

use App\Repositories\Repository;

/**
 * Interface VehiclesRepositoryInterface
 * @package App\Repositories\Website\TowingCapacity
 */
interface VehiclesRepositoryInterface extends Repository
{
    public function deleteAll();
}
