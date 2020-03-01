<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Manufacturer;

class ManufacturerTransformer extends TransformerAbstract
{
    public function transform($manufacturer)
    {                
        
        if (isset($manufacturer->manufacturer)) {
            $manufacturer = $manufacturer->manufacturer;
        }
        
	 return [
             'id' => (int)$manufacturer->id,
             'name' => $manufacturer->name
         ];
    }
}