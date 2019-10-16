<?php

namespace App\Transformers\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Category;

class CategoryTransformer extends TransformerAbstract
{
    public function transform(Category $category)
    {                
	 return [
             'id' => (int)$category->id,
             'name' => $category->name
         ];
    }
}