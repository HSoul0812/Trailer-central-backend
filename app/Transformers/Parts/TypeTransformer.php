<?php

namespace App\Transformers\Parts;

use App\Models\Parts\Type;
use League\Fractal\TransformerAbstract;

class TypeTransformer extends TransformerAbstract 
{
    public function transform($type): array
    {
	     return [
             'id' => (int)$type->id,
             'name' => $type->name,
             'types' => $type->categories
         ];
    }
}