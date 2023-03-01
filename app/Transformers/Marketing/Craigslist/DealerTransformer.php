<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Services\Dispatch\Craigslist\DTOs\DealerCraigslist;
use League\Fractal\TransformerAbstract;

/**
 * Class DealerTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class DealerTransformer extends TransformerAbstract
{
    /**
     * @param DealerCraigslist $clapp
     * @return array
     */
    public function transform(DealerCraigslist $clapp): array
    {
        return [
            'dealer' => [
                'id'    => $clapp->dealerId,
                'name'  => $clapp->dealerName,
                'email' => $clapp->dealerEmail,
                'type'  => $clapp->dealerType,
                'state' => $clapp->dealerState
            ],
            'type'                 => $clapp->type,
            'slots'                => $clapp->slots,
            'chrome_mode'          => $clapp->chrome_mode,
            'marketing_enabled_at' => $clapp->since,
            'next_scheduled'       => $clapp->next
        ];
    }
}