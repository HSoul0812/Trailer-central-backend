<?php

namespace App\Transformers\Website\TowingCapacity;

use App\Models\Website\TowingCapacity\Make;
use League\Fractal\TransformerAbstract;

/**
 * Class MakeTransformer
 * @package App\Transformers\Website\TowingCapacity
 */
class MakeTransformer extends TransformerAbstract
{
    /**
     * @param Make $make
     * @return array
     */
    public function transform(Make $make)
    {
        return [
            'id' => (int)$make->id,
            'name' => $make->name,
        ];
    }
}
