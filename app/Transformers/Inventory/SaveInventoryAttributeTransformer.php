<?php

namespace App\Transformers\Inventory;

use App\Http\Requests\Request;
use League\Fractal\TransformerAbstract;

/**
 * Class SaveInventoryAttributeTransformer
 *
 * @package App\Transformers\Inventory
 */
class SaveInventoryAttributeTransformer implements TransformerAbstract
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function transform(Request $request): array
    {
        return [
            'inventoryId' => $request->id,
            'attributes' => $request->attributes,
        ];
    }
}
