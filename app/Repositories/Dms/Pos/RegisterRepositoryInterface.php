<?php

namespace App\Repositories\Dms\Pos;

use App\Repositories\Repository;

interface RegisterRepositoryInterface extends Repository
{
    public function getAllByDealerId(int $dealerId);
}
