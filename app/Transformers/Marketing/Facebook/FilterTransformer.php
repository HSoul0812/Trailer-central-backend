<?php

namespace App\Transformers\Marketing\Facebook;

use App\Models\Marketing\Facebook\Filter;
use League\Fractal\TransformerAbstract;

class FilterTransformer extends TransformerAbstract
{
    public function transform(Filter $filter)
    {
        return [
            'id' => $filter->id,
            'marketplace_id' => $filter->marketplace_id,
            'type' => $filter->filter_type,
            'value' => $filter->filter,
            'created_at' => $filter->created_at,
            'updated_at' => $filter->updated_at
        ];
    }
}
