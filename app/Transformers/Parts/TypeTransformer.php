<?php

declare(strict_types=1);

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;

class TypeTransformer extends TransformerAbstract
{
    public function transform($type): array
    {
        return [
             'id' => (int) $type->id,
             'name' => $type->name,
             'categories' => $type->categories,
         ];
    }
}
