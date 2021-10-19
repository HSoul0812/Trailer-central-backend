<?php


namespace App\Transformers\User;


use App\DealerLocationMileageFee;
use League\Fractal\TransformerAbstract;

class DealerLocationMileageFeeTransformer extends TransformerAbstract
{
    public function transform(DealerLocationMileageFee $fee): array
    {
        return $fee->toArray();
    }
}
