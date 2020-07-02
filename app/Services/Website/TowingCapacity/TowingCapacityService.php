<?php

namespace App\Services\Website\TowingCapacity;

use App\Repositories\Website\TowingCapacity\MakesRepositoryInterface;
use App\Repositories\Website\TowingCapacity\VehiclesRepositoryInterface;

/**
 * Class TowingCapacityService
 * @package App\Services\Website\TowingCapacity
 */
class TowingCapacityService
{
    /**
     * @var VehiclesRepositoryInterface
     */
    private $vehicleRepository;

    /**
     * @var MakesRepositoryInterface
     */
    private $makesRepository;
}
