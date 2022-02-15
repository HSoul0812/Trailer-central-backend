<?php

namespace App\Transformers\Dms\ServiceOrder;

use App\Models\CRM\Dms\ServiceOrder\Type;
use League\Fractal\TransformerAbstract;

/**
 * Class TypeTransformer
 * @package App\Transformers\Dms\ServiceOrder
 */
class TypeTransformer extends TransformerAbstract
{
    public function transform(Type $item)
    {
        return [
            'id' => (int)$item->id,
            'name' => $item->name,
            'title' => $item->title,
            'sort_order' => $item->sort_order,
        ];
    }
}
