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
        
    private $queryString = '';
    
    public function __construct() 
    {
        $requestData = app('request')->all(); 
        
        foreach($requestData as $key => $value) {
            if ($key === 'dealer_id') {
                continue;
            }
            
            if (is_array($value)) {
                foreach($value as $index => $val) {
                    if ($index === 0 && empty($this->queryString)) {
                        $this->queryString .= "?{$key}[]=$val";
                    } else {
                        $this->queryString .= "&{$key}[]=$val";
                    }
                }
           }
           
           
        }
    }
        
    
    public function transform(Filter $filter)
    {                
        $request = app('request');
        
        $ret = [
             'id' => (int)$filter->id,
             'attribute' => $filter->attribute,
             'label' => ($filter->attribute == 'brand') ? 'Brand' : $filter->label,
             'type' => ($filter->attribute == 'brand') ? 'select' : $filter->type,
             'is_eav' => 0,
             'position' => $filter->position,
             'sort' => $filter->sort,
             'sort_dir' => $filter->sort_dir,
             'prefix' => $filter->prefix,
             'suffix' => $filter->suffix,
             'step' => $filter->step,
             'dependancy' => $filter->dependancy,
             'is_visible' => (int)$filter->is_visible,
             'global' => false,             
             'state' => $this->getFilterState($filter),
         ];
        
        if ($filter->type == 'select' || $filter->attribute == 'brand') {
            $ret['values'] = $this->getFilterValues($filter);
        }
        
        if ($filter->type == 'search') {
            if ($request->has($filter->attribute)) {
                $ret['value'] = $request->get($filter->attribute);
            } else {
                $ret['value'] = '';
            }
        }
        
        if ($filter->type == 'slider') {
            $ret['min'] = $this->getMinPriceFilter($filter);
            $ret['max'] = $this->getMaxPriceFilter($filter);
        }
        
        return $ret;
    }
    
    
    private function getFilterValues(Filter $filter)
    {        
    
        $requestData = app('request')->all();

        if (empty($requestData['dealer_id']) || !isset($this->attributeModelIdMapping[$filter->attribute])) {
            return [];
        }

        $query = Part::with($filter->attribute)
                       ->where('show_on_website', 1);
        
        $query = $this->addFiltersToQuery($query, $requestData, $this->attributeModelIdMapping[$filter->attribute]);
        
        $parts = $query->whereNotNull($this->attributeModelIdMapping[$filter->attribute])
                          ->groupBy($this->attributeModelIdMapping[$filter->attribute])
                          ->get();
                          
        $values = [];
          
        foreach($parts as $part) {
            
            $count = $this->getPartsCount($filter, $part);
            $status = 'selectable';
            
            if (isset($requestData[$this->attributeModelIdMapping[$filter->attribute]])) {
                foreach($requestData[$this->attributeModelIdMapping[$filter->attribute]] as $id) {
                    if ($part->{$filter->attribute}->id == $id) {
                        $status = 'selected';
                        break;
                    } 
                }
            }
            
            $actionQuery = "{$this->attributeModelIdMapping[$filter->attribute]}[]={$part->{$filter->attribute}->name}";
            
            if (empty($this->queryString)) {
                $queryString = "?$actionQuery";
            } else {
                if ($status === 'selected') {
                    $queryString = str_replace($actionQuery.'&', '', $this->queryString);
                    $queryString = str_replace('&'.$actionQuery, '', $queryString);
                    $queryString = str_replace('?'.$actionQuery, '', $queryString);   
                    $queryString = str_replace($actionQuery, '', $queryString);  
                } else {
                    $queryString = $this->queryString."&$actionQuery";
                }                
            }
            
            
            $values[] = [
                'id' => $part->{$filter->attribute}->id,
                'label' => $part->{$filter->attribute}->name,
                'value' => $part->{$filter->attribute}->name,
                'count' => $count, 
                'base' => 0, // What is this?
                'status' => $status,                        
                'action' => $queryString
            ];
                
        }
    
        return $values;
    }
    
    private function getFilterState(Filter $filter) {
        return false;
    }
    
    private function addFiltersToQuery($query, $requestData, $attribute) {
        foreach($this->attributeModelIdMapping as $value) {
            if (isset($requestData[$value]) && $value != $attribute) {               
                $query = $query->whereIn($value, $requestData[$value]);
            }
        }        
        
        return $query;
    }
    
    private function getMaxPriceFilter(Filter $filter) {
        if ($filter->attribute != 'price') {
            return null;
        }
        
        $requestData = app('request')->only('category_id', 'type_id', 'dealer_id', 'brand_id');        
        $query = Part::whereIn('dealer_id', $requestData['dealer_id'])->where('price', '>', 0);
        
        foreach ($requestData as $attributeName => $attributeValues) {             
            $query = $query->whereIn($attributeName, $attributeValues);
        }
        
        $part = $query->orderBy('price', 'DESC')->first();
        
        if ($part) {
            return $part->price;
        }
        
        return 0;
    }
    
    private function getMinPriceFilter(Filter $filter) {
        if ($filter->attribute != 'price') {
            return null;
        }
        
        $requestData = app('request')->only('category_id', 'type_id', 'dealer_id', 'brand_id');        
        $query = Part::whereIn('dealer_id', $requestData['dealer_id'])->where('price', '>', 0);
        
        foreach ($requestData as $attributeName => $attributeValues) {             
            $query = $query->whereIn($attributeName, $attributeValues);
        }
        
        $part = $query->orderBy('price', 'ASC')->first();
        
        if ($part) {
            return $part->price;
        }
        
        return 0;
    }
    
    private function getPartsCount($filter, $part) {        
        
        $requestData = app('request')->only('category_id', 'type_id', 'dealer_id', 'brand_id');
        $dealerId = $requestData['dealer_id'];        
        
        $query = Part::whereIn('dealer_id', $dealerId)
                    ->where($this->attributeModelIdMapping[$filter->attribute], $part->{$filter->attribute}->id);
        
        foreach ($requestData as $attributeName => $attributeValues) {
            if ( ($this->attributeModelIdMapping[$filter->attribute] == $attributeName) ) {
                continue;
            }
             
            $query = $query->whereIn($attributeName, $attributeValues);
        }
                
        return $query->count();
    }
    
    
}