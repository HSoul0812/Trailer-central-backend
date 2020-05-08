<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Vendor;

class VendorTransformer extends TransformerAbstract
{
    public function transform($vendor)
    {   
        if (isset($vendor->vendor)) {
            $vendor = $vendor->vendor;
        }
        
	 return [
             'id' => (int)$vendor->id,
             'name' => $vendor->name
         ];
    }
} 