<?php

namespace App\Transformers\Website\TowingCapacity;

use App\Models\Website\TowingCapacity\Vehicle;
use League\Fractal\TransformerAbstract;

/**
 * Class ModelTransformer
 * @package App\Transformers\Website\TowingCapacity
 */
class ModelTransformer extends TransformerAbstract
{
    /**
     * @param Vehicle $vehicle
     * @return array
     */
    public function transform(Vehicle $vehicle)
    {
        return [
            'model' => $vehicle->model,
        ];
    }
}
