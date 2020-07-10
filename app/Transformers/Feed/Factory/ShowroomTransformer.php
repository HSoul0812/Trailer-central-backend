<?php

namespace App\Transformers\Feed\Factory;

use League\Fractal\TransformerAbstract;
use App\Models\Showroom\Showroom;

class ShowroomTransformer extends TransformerAbstract
{

    public function transform(Showroom $showroom)
    {   
        return [
            'id' => $showroom->id,
            'manufacturer' => $showroom->manufacturer,
            'model' => $showroom->model,
        ];
    }
} 