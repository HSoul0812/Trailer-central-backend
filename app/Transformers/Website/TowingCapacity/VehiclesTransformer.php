<?php

namespace App\Transformers\Website\TowingCapacity;

use App\Models\Website\TowingCapacity\Vehicle;
use League\Fractal\TransformerAbstract;

/**
 * Class VehiclesTransformer
 * @package App\Transformers\Website\TowingCapacity
 */
class VehiclesTransformer extends TransformerAbstract
{
    /**
     * @param Vehicle $vehicle
     * @return array
     */
    public function transform(Vehicle $vehicle)
    {
        return [
            'id' => $vehicle->id,
            'year' => $vehicle->year,
            'make_id' => $vehicle->make_id,
            'model' => $vehicle->model,
            'sub_model' => $vehicle->sub_model,
            'drive_train' => $vehicle->drive_train,
            'engine' => $vehicle->engine,
            'tow_limit' => $vehicle->tow_limit,
        ];
    }
}
