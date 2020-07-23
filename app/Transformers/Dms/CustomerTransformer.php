<?php

namespace App\Transformers\Dms;

use League\Fractal\TransformerAbstract;

class CustomerTransformer extends TransformerAbstract
{

    public function transform($customer)
    {           
        return [ 
            'id' => $customer->id,
            'name' => trim($customer->first_name)." ".trim($customer->last_name)
        ];
    }
} 