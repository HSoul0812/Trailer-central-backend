<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;

/**
 * @author Marcel
 */
class CycleCountTransformer extends TransformerAbstract
{
    public function transform($cycleCount)
    {
        return [
            'id' => (int) $cycleCount->id,
            'dealer_id' => (int) $cycleCount->dealer_id,
            'is_completed' => (int) $cycleCount->is_completed,
            'is_balanced' => (int) $cycleCount->is_balanced,
            'count_date' => $cycleCount->count_date,
            'bin_id' => $cycleCount->bin_id,
            'parts' => $cycleCount->parts
        ];
    }
}
