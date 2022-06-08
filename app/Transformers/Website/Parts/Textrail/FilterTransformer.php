<?php

namespace App\Transformers\Website\Parts\Textrail;

use League\Fractal\TransformerAbstract;
use App\Models\Parts\Filter;
use App\Models\Parts\Textrail\Part;
use App\Models\Parts\Textrail\Brand;
use App\Models\Parts\Textrail\Category;
use App\Models\Parts\Textrail\Type;

class FilterTransformer extends TransformerAbstract
{

    private $attributeModelIdMapping = [
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

            if (is_array($value)) {
                foreach($value as $index => $val) {
                    $delimiter = empty($this->queryString) ? '?' : '&';

                    if (!is_array($val)) {
                        $this->queryString .= "{$delimiter}{$key}[]=".urlencode($val);
                        continue;
                    }

                    foreach ($val as $param) {
                        $this->queryString .= "{$delimiter}{$key}[]=".urlencode($param);
                    }
                }
           }

           if (is_array($value)) {
               foreach($value as $index => $val) {
                   $param = is_array($val) ? reset($val) : $val;

                   if ($key == 'type_id') {
                       $this->mappedTypes[$key][] = Type::where('name', $param)->first()->id;
                   } else if ($key == 'brand_id') {
                       $this->mappedTypes[$key][] = Brand::where('name', $param)->first()->id;
                   } else if ($key == 'category_id') {
                       $this->mappedTypes[$key][] = Category::where('name', $param)->first()->id;
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
             'field' => $filter->field,
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
        $hiddenFilters = [];
        if (isset($requestData['hidden_filter'])) {
            $hiddenFilters = $this->parseHiddenFilters($requestData['hidden_filter']);
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

            if ($filter->attribute === 'subcategory' && isset($requestData['subcategory']) && in_array($part->subcategory, $requestData['subcategory'])) {
                $status = 'selected';
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

            $queryString = empty($queryString) ? '?' : $queryString;

            if ($filter->attribute == 'subcategory') {
                if (isset($hiddenFilters[$this->attributeModelIdMapping[$filter->attribute]])) {
                    if (isset($hiddenFilters[$this->attributeModelIdMapping[$filter->attribute]][$part->{$filter->attribute}])) {
                        continue;
                    }
                }

                $values[] = [
                    'id' => 0,
                    'parent' => '',
                    'label' => $part->{$filter->attribute},
                    'value' => $part->{$filter->attribute},
                    'count' => $count,
                    'base' => 0, // What is this?
                    'status' => $status,
                    'action' => $queryString
                ];
            } else {
                if (isset($hiddenFilters[$this->attributeModelIdMapping[$filter->attribute]])) {
                    if (isset($hiddenFilters[$this->attributeModelIdMapping[$filter->attribute]][$part->{$filter->attribute}->name])) {
                        continue;
                    }
                }

                $parent = '';
                if ($filter->attribute === 'category') {
                    $type = Type::find($part->{$filter->attribute}->parent_id);
                    if (!empty($type)) {
                        $parent = Type::find($part->{$filter->attribute}->parent_id)->name;
                    }
                }

                $values[] = [
                    'id' => $part->{$filter->attribute}->id,
                    'parent' => $parent,
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

        foreach($this->attributeModelIdMapping as $value) {
            if (isset($requestData[$value]) && $value != $attribute) {
                if (isset($this->mappedTypes[$value])) {
                    $query = $query->whereIn($value, $this->mappedTypes[$value]);
                } else {
                    $query = $query->whereIn($value, $requestData[$value]);
                }
            }
        }

        return $query;
    }

    private function getMaxPriceFilter(Filter $filter) {
        if ($filter->attribute != 'price') {
            return null;
        }

        $requestData = app('request')->only('category_id', 'type_id', 'brand_id');
        $query = Part::where('price', '>', 0);

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

        $requestData = app('request')->only('category_id', 'type_id', 'brand_id');
        $query = Part::where('price', '>', 0);

        foreach ($this->mappedTypes as $attributeName => $attributeValues) {
            $query = $query->whereIn($attributeName, $attributeValues);
        }

        $part = $query->orderBy('price', 'ASC')->first();

        if ($part) {
            return $part->price;
        }

        return 0;
    }

    private function getPartsCount($filter, $part)
    {
        $requestData = app('request')->only('category_id', 'type_id', 'brand_id', 'show_on_website');

        if ($filter->attribute == 'subcategory') {
            $query = Part::where($this->attributeModelIdMapping[$filter->attribute], $part->{$filter->attribute});
        } else {
            $query = Part::where($this->attributeModelIdMapping[$filter->attribute], $part->{$filter->attribute}->id);
        }

        if (isset($requestData['show_on_website'])) {
            $query->where('show_on_website', $requestData['show_on_website']);
        }

        foreach ($this->mappedTypes as $attributeName => $attributeValues) {
            if ( ($this->attributeModelIdMapping[$filter->attribute] == $attributeName) ) {
                continue;
            }

            $query = $query->whereIn($attributeName, $attributeValues);
        }

        return $query->count();
    }

    private function parseHiddenFilters($hiddenFilters) {
        $filters = [];
        foreach($hiddenFilters as $filter) {
            if (empty($filter)) {
                continue;
            }
            $explodedFilter = explode('|', $filter);

            if (isset($filters[$explodedFilter[0]])) {
                $filters[$explodedFilter[0]][$explodedFilter[1]] = true;
            } else {
                $filters[$explodedFilter[0]] = [];
                $filters[$explodedFilter[0]][$explodedFilter[1]] = true;
            }
        }
        return $filters;
    }


}
