<?php

namespace App\Transformers\ViewedDealer;

use App\DTOs\Dealer\TcApiResponseDealer;
use League\Fractal\TransformerAbstract;

class TcApiResponseDealerTransformer extends TransformerAbstract
{
    public function __construct(
    ) {
    }

    public function transform(TcApiResponseDealer $dealer): array
    {
        return [
            'id' => $dealer->id,
            'name' => $dealer->name,
            'location_id' => $dealer->dealer_location_id,
            'location_name' => $dealer->location_name,
            'region' => $dealer->region,
            'city' => $dealer->city,
            'zip' => $dealer->zip,
        ];
    }
}
