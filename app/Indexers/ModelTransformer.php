<?php

namespace App\Indexers;

use Illuminate\Database\Eloquent\Model;

class ModelTransformer implements Transformer
{
    /**
     * @param Model $model
     * @return array
     */
    public function transform($model): array
    {
        return $model->toArray();
    }
}
