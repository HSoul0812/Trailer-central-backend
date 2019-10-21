<?php

namespace App\Transformers\Website\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Filter;
use Illuminate\Support\Facades\Cache;
use App\Models\Parts\Part;

class FilterTransformer extends TransformerAbstract
{
    
    private $attributeModelIdMapping = [
        'dealer' => 'dealer_id',
        'type' => 'type_id',
        'category' => 'category_id',
        'manufacturer' => 'manufacturer_id',
        'brand' => 'brand_id'
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
        
        $requestData = app('request')->all();

        if (empty($requestData['dealer_id']) || !isset($this->attributeModelIdMapping[$filter->attribute])) {
            return [];
        }

        $query = Part::with($filter->attribute)
                       ->where('show_on_website', 1);
        
        $query = $this->addFiltersToQuery($query, $requestData);
        
        $parts = $query->whereNotNull($this->attributeModelIdMapping[$filter->attribute])
                          ->groupBy($this->attributeModelIdMapping[$filter->attribute])
                          ->get();
                          
        $values = [];
          
        foreach($parts as $part) {
            
            $count = Part::whereIn('dealer_id', $requestData['dealer_id'])->where($this->attributeModelIdMapping[$filter->attribute], $part->{$filter->attribute}->id)->count();

            $values[] = [
                'id' => $part->{$filter->attribute}->id,
                'label' => $part->{$filter->attribute}->name,
                'value' => $part->{$filter->attribute}->name,
                'count' => $count, 
                'base' => 0, // What is this?
                'status' => 'selectable'
            ];
        }
                
        return $values;
    }
    
    private function addFiltersToQuery($query, $requestData) {
        foreach($this->attributeModelIdMapping as $value) {
            if (isset($requestData[$value])) {
                $query = $query->whereIn($value, $requestData[$value]);
            }
        }        
        
        return $query;
    }
    
}