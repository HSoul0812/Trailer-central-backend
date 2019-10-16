<?php

namespace App\Transformers\Website\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Filter;

class FilterTransformer extends TransformerAbstract
{
    public function transform(Filter $filter)
    {             
//         "id": "18",
//        "attribute": "sku",
//        "label": "Search By SKU #",
//        "type": "search",
//        "is_eav": "0",
//        "position": "-1",
//        "sort": null,
//        "sort_dir": null,
//        "prefix": null,
//        "suffix": null,
//        "step": null,
//        "dependancy": null,
//        "is_visible": "1",
//        "is_active": null,
//        "action": "?",
//        "state": [],
//        "value": "",
//        "global": true,
//        "is_selected": false
        
        
	 return [
             'id' => (int)$filter->id,
             'attribute' => $filter->attribute,
             'label' => $filter->label,
             'type' => $filter->type,
             'is_eav' => 0,
             'position' => $filter->position,
             'sort' => $filter->sort,
             'sort_dir' => $filter->sort_dir,
             'prefix' => $filter->prefix,
             'suffix' => $filter->suffix,
             'step' => $filter->step,
             'dependancy' => $filter->dependancy,
             'is_visible' => (int)$filter->is_visible
         ];
    }
}