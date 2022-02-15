<?php

namespace App\Repositories\Dms;

use App\Repositories\Repository;

/**
 * Interface UnitSaleLaborRepositoryInterface
 * @package App\Repositories\Dms
 */
interface UnitSaleLaborRepositoryInterface extends Repository
{
    public function serviceReport($params): array;
    public function getTechnicians($params): array;
}
