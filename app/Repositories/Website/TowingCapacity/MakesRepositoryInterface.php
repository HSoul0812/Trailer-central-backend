<?php

namespace App\Repositories\Website\TowingCapacity;

use App\Repositories\Repository;

/**
 * Interface MakesRepositoryInterface
 * @package App\Repositories\Website\TowingCapacity
 */
interface MakesRepositoryInterface extends Repository
{
    /**
     * @param string $year
     * @return mixed
     */
    public function getByYear(string $year);

    public function deleteAll();
}
