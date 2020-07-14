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
            'category' => $showroom->type,
            'model' => $showroom->model,
            'year' => $showroom->year,
            'msrp' => $showroom->msrp,
            'description' => $showroom->description,
            'description_txt' => $showroom->description_txt,
        ];
    }
} 