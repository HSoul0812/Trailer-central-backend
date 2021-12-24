<?php

namespace App\Transformers\Inventory;

use App\Http\Requests\Request;
use App\Transformers\TransformerInterface;

/**
 * Class SaveInventoryAttributeTransformer
 *
 * @package App\Transformers\Inventory
 */
class SaveInventoryAttributeTransformer implements TransformerInterface
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function transform(array $params): array
    {
        return [
            'inventory_id' => $params['inventory_id'],
            'dealer_id' => $params['dealer_id'],
            'attributes' => $params['attributes'],
        ];
    }
}
