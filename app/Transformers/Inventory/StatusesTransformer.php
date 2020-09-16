<?php

namespace App\Transformers\Inventory;

use App\Models\Inventory\Status;
use League\Fractal\TransformerAbstract;

/**
 * Class StatusesTransformer
 * @package App\Transformers\Inventory
 */
class StatusesTransformer extends TransformerAbstract
{
    /**
     * @param Status $status
     * @return array
     */
    public function transform(Status $status)
    {
        return [
            'id' => $status->id,
            'name' => $status->name,
            'label' => $status->label,
        ];
    }
}
