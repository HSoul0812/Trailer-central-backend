<?php

namespace App\Repositories\Dms\Pos;

use App\Repositories\Repository;

interface RegisterRepositoryInterface extends Repository
{
    /**
     * Searches all the outlets with open registers by dealer_id
     *
     * @param int $dealerId
     * @return mixed array<Outlet>
     */
    public function getAllByDealerId(int $dealerId);

    /**
     * Validates if a register is already open for requested outlet
     *
     * @param int $outletId
     * @return bool
     */
    public function hasOpenRegister(int $outletId): bool;
}
