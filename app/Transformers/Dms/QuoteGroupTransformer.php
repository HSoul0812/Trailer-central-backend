<?php

namespace App\Transformers\Dms;

use League\Fractal\TransformerAbstract;

class QuoteGroupTransformer extends TransformerAbstract
{

    public function transform($group)
    {   
        $transformData = [];

        foreach ($group as $item) {
            if ($item['deal'] === 0) {
                $status = 'quotes';
            } else if ($item['completed_deal'] === 0) {
                $status = 'deals';
            } else {
                $status = 'completed_deals';
            }
            $transformData[$status] = [
                'front_gross' => round($item['totalFrontGross'], 2),
                'qty' => $item['totalQty']
            ];
        }
        
        return ['totals' => $transformData];
    }
} 