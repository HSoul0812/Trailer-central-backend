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

    /**
     * TowingCapacityService constructor.
     * @param VehiclesRepositoryInterface $vehicleRepository
     * @param MakesRepositoryInterface $makesRepository
     */
    public function __construct(VehiclesRepositoryInterface $vehicleRepository, MakesRepositoryInterface $makesRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->makesRepository = $makesRepository;
    }

    /**
     * @param int $year
     * @return mixed
     */
    public function getMakes(int $year)
    {
        return $this->makesRepository->getByYear($year);
    }

    /**
     * @param int $year
     * @param int $makeId
     * @return mixed
     */
    public function getVehicleModels(int $year, int $makeId)
    {
        return $this->vehicleRepository->getModels($year, $makeId);
    }

    /**
     * @param int $year
     * @param int $makeId
     * @return mixed
     */
    public function getVehicles(int $year, int $makeId, string $model)
    {
        $params = [
            'year' => $year,
            'makeId' => $makeId,
            'model' => $model,
        ];

        return $this->vehicleRepository->getAll($params);
    }
}
