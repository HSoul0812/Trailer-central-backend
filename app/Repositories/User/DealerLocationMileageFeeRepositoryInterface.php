<?php


namespace App\Repositories\User;


use App\DealerLocationMileageFee;
use App\Repositories\Repository;

interface DealerLocationMileageFeeRepositoryInterface extends Repository
{
    public function update($params): DealerLocationMileageFee;
}
