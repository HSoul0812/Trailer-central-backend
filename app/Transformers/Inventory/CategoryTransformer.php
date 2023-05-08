<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\Category;

class CategoryTransformer extends TransformerAbstract
{

    public function transform(Category $category)
    {
	    return [
            'id' => $category->inventory_category_id,
            'entity_type_id' => $category->entity_type_id,
            'entity_type' => $category->entityType,
            'category' => $category->category,
            'label' => $category->label,
            'legacy_category' => $category->legacy_category,
            'website_label' => $category->website_label,
            'alt_category' => $category->alt_category,
            'alt_label' => $category->alt_label,
        ];
    }

}
