<?php

namespace App\Transformers\Dms;

use League\Fractal\TransformerAbstract;

class QuoteTotalsTransformer extends TransformerAbstract
{

    public function transform($totals)
    {   
        $transformData = [
            'quotes' => [
                'front_gross' => 0,
                'qty' => 0
            ],
            'deals' => [
                'front_gross' => 0,
                'qty' => 0
            ],
            'completed_deals' => [
                'front_gross' => 0,
                'qty' => 0
            ]
        ];

        foreach ($totals as $item) {
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
        
        return $transformData;
    }
} 