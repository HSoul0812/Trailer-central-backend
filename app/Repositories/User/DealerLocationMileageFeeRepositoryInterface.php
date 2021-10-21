<?php


namespace App\Repositories\User;


use App\Models\User\DealerLocationMileageFee;
use App\Repositories\Repository;

interface DealerLocationMileageFeeRepositoryInterface extends Repository
{
    public function update($params): DealerLocationMileageFee;
}
