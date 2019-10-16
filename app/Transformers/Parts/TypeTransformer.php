<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Type;

class TypeTransformer extends TransformerAbstract
{
    public function transform(Type $type)
    {                
	 return [
             'id' => (int)$type->id,
             'name' => $type->name
         ];
    }
}