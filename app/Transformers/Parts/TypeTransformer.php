<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Type;

class TypeTransformer extends TransformerAbstract
{
    public function transform($type)
    {                
        
        if (isset($type->type)) {
            $type = $type->type;
        }
        
	 return [
             'id' => (int)$type->id,
             'name' => $type->name
         ];
    }
} 