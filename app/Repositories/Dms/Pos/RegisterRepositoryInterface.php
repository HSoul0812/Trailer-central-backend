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
}
