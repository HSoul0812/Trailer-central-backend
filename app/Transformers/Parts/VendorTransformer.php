<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Vendor;
use App\Models\Parts\Part;

class VendorTransformer extends TransformerAbstract
{
    public function transform($vendor)
    {   
        if ($vendor instanceof Part) {
            $vendor = $vendor->vendor;
        }
        
	 return [
             'id' => (int)$vendor->id,
             'name' => $vendor->name
         ];
    }
} 