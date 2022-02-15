<?php

namespace App\Transformers;

use App\Services\Common\DTOs\SimpleData;
use League\Fractal\TransformerAbstract;

class SimpleTransformer extends TransformerAbstract {
    /**
     * Transform Simple Data
     * 
     * @param SimpleData $data
     * @return array
     */
    public function transform(SimpleData $data)
    {        
        return [
            'id' => $data->getIndex(),
            'name' => $data->getName()
        ];
    }
}