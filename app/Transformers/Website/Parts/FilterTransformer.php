<?php

namespace App\Transformers\Website\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Filter;
use Illuminate\Support\Facades\Cache;
use App\Models\Parts\Part;

class FilterTransformer extends TransformerAbstract
{
    
    private $attributeModelIdMapping = [
        'type' => 'type_id',
        'category' => 'category_id',
        'manufacturer' => 'manufacturer_id'
    ];
    
    public function transform(Filter $filter)
    {                           
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
             'is_visible' => (int)$filter->is_visible,
             'values' => $this->getFilterValues($filter)
         ];
    }
    
    
    private function getFilterValues(Filter $filter)
    {
        $dealerId = Cache::get(env('DEALER_ID_KEY', 'api_dealer_id'));
        
        if (empty($dealerId) || !isset($this->attributeModelIdMapping[$filter->attribute])) {
            return [];
        }
        
        $parts = Part::with($filter->attribute)
                        ->whereIn('dealer_id', $dealerId)
                        ->whereNotNull($this->attributeModelIdMapping[$filter->attribute])
                        ->groupBy($this->attributeModelIdMapping[$filter->attribute])
                        ->get();
        
        $values = [];
          
        foreach($parts as $part) {
            
            $count = Part::whereIn('dealer_id', $dealerId)->where($this->attributeModelIdMapping[$filter->attribute], $part->{$filter->attribute}->id)->count();
            
            $values[] = [
                'label' => $part->{$filter->attribute}->name,
                'value' => $part->{$filter->attribute}->name,
                'count' => $count, // do Query
                'base' => 0, // What is this?
                'status' => 'selectable'
            ];
        }
                
        return $values;
    }
    
}