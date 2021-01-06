<?php

namespace App\Repositories\Dms\ServiceOrder;

use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface UnitSaleLaborRepositoryInterface
 * @package App\Repositories\Dms
 */
interface UnitSaleLaborRepositoryInterface extends Repository
{
    /**
     * @param $params
     * @return Collection
     */
    public function serviceReport($params): Collection;
}
