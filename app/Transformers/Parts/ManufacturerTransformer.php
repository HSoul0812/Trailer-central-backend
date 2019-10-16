<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Manufacturer;

class ManufacturerTransformer extends TransformerAbstract
{
    public function transform(Manufacturer $manufacturer)
    {                
	 return [
             'id' => (int)$manufacturer->id,
             'name' => $manufacturer->name
         ];
    }
}