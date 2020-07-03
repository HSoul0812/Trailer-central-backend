<?php

namespace App\Transformers\Website\TowingCapacity;

use App\Models\Website\TowingCapacity\Vehicle;
use League\Fractal\TransformerAbstract;

/**
 * Class YearsTransformer
 * @package App\Transformers\Website\TowingCapacity
 */
class YearsTransformer extends TransformerAbstract
{
    /**
     * @param Vehicle $vehicle
     * @return array
     */
    public function transform(Vehicle $vehicle)
    {
        return [
            'year' => $vehicle->year,
        ];
    }
}
