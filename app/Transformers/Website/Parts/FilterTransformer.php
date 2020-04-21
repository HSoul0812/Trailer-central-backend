<?php

namespace App\Transformers\Website\Parts;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Filter;
use Illuminate\Support\Facades\Cache;
use App\Models\Parts\Part;
use App\Models\Parts\Brand;
use App\Models\Parts\Category;
use App\Models\Parts\Type;
class FilterTransformer extends TransformerAbstract
{
    
    private $attributeModelIdMapping = [
        'dealer' => 'dealer_id',
        'type' => 'type_id',
        'category' => 'category_id',
        'manufacturer' => 'manufacturer_id',
        'brand' => 'brand_id',
        'subcategory' => 'subcategory'
    ];
        
    private $queryString = '';
    
    private $mappedTypes = [];
    
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
                        $this->queryString .= "?{$key}[]=".urlencode($val);
                    } else {
                        $this->queryString .= "&{$key}[]=".urlencode($val);
                    }
                }
           }
           
           if (is_array($value)) {
               foreach($value as $index => $val) {
                   if ($key == 'type_id') {
                       
                       $this->mappedTypes[$key][] = Type::where('name', $val)->first()->id;
                       
                   } else if ($key == 'brand_id') {
                       $this->mappedTypes[$key][] = Brand::where('name', $val)->first()->id;
                   } else if ($key == 'category_id') {
                       $this->mappedTypes[$key][] = Category::where('name', $val)->first()->id;
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
        
        if ($filter->attribute == 'subcategory') {
            $query = Part::where('show_on_website', 1);
        } else {
            $query = Part::with($filter->attribute)
                       ->where('show_on_website', 1);
        }        
        
        $query = $this->addFiltersToQuery($query, $requestData, $this->attributeModelIdMapping[$filter->attribute]);
        
        $parts = $query->whereNotNull($this->attributeModelIdMapping[$filter->attribute])
                          ->groupBy($this->attributeModelIdMapping[$filter->attribute])
                          ->get();
                          
        $values = [];
          
        foreach($parts as $part) {
            
            $count = $this->getPartsCount($filter, $part);
            $status = 'selectable';
            
            if (isset($this->mappedTypes[$this->attributeModelIdMapping[$filter->attribute]])) {
                foreach($this->mappedTypes[$this->attributeModelIdMapping[$filter->attribute]] as $id) {
                    if ($part->{$filter->attribute}->id == $id) {
                        $status = 'selected';
                        break;
                    } 
                }
            }
            
            if ($filter->attribute == 'subcategory') {
                $actionQuery = "{$this->attributeModelIdMapping[$filter->attribute]}[]=".urlencode($part->{$filter->attribute});
            } else {
                $actionQuery = "{$this->attributeModelIdMapping[$filter->attribute]}[]=".urlencode($part->{$filter->attribute}->name);
            }
                        
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
            
            if ($filter->attribute == 'subcategory') {
                $values[] = [
                    'id' => 0,
                    'label' => $part->{$filter->attribute},
                    'value' => $part->{$filter->attribute},
                    'count' => $count, 
                    'base' => 0, // What is this?
                    'status' => $status,                        
                    'action' => $queryString
                ];
            } else {
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
            
        }
    
        return $values;
    }
    
    private function getFilterState(Filter $filter) {
        return false;
    }
    
    private function addFiltersToQuery($query, $requestData, $attribute) {
        $query = $query->whereIn('dealer_id', $requestData['dealer_id']);
        foreach($this->attributeModelIdMapping as $value) {
            if (isset($requestData[$value]) && $value != $attribute) {               
                if ($value != 'dealer_id') {
                    $query = $query->whereIn($value, $this->mappedTypes[$value]);
                }
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
        
        foreach ($this->mappedTypes as $attributeName => $attributeValues) {             
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
        
        foreach ($this->mappedTypes as $attributeName => $attributeValues) {             
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
        
        if ($filter->attribute == 'subcategory') {
            $query = Part::whereIn('dealer_id', $dealerId)
                    ->where($this->attributeModelIdMapping[$filter->attribute], $part->{$filter->attribute});
        } else {
            $query = Part::whereIn('dealer_id', $dealerId)
                    ->where($this->attributeModelIdMapping[$filter->attribute], $part->{$filter->attribute}->id);
        }
        
        foreach ($this->mappedTypes as $attributeName => $attributeValues) {
            if ( ($this->attributeModelIdMapping[$filter->attribute] == $attributeName) ) {
                continue;
            }
             
            $query = $query->whereIn($attributeName, $attributeValues);
        }
                
        return $query->count();
    }
    
    
}