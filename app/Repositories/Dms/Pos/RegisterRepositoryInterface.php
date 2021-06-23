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
     * Opens new register for the given outlet
     *
     * @param array $params
     * @return bool
     */
    public function open(array $params): bool;

    /**
     * Validates if a register is already open for requested outlet
     *
     * @param int $outletId
     * @return bool
     */
    public function isRegisterOpen(int $outletId): bool;
}
