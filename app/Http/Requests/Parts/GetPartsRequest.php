<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 *  
 * @author Eczek
 */
class GetPartsRequest extends Request {
    
    protected $rules = [
        'per_page' => 'integer',
        'sort' => 'in:price,-price,relevance,title,-title,length,-length',
        'type_id' => 'array',
        'type_id.*' => 'integer|type_exists',
        'category_id' => 'array',
        'category_id.*' => 'integer|category_exists',
        'manufacturer_id.*' => 'integer|manufacturer_exists',
        'manufacturer_id' => 'array',
        'brand_id' => 'array',
        'brand_id.*' => 'integer|brand_exists',
        'price' => 'price_format',
        'dealer_id' => 'array',
        'dealer_id.*' => 'integer'
    ];
    
    public function all($keys = null) {
        $all = parent::all($keys);
        
        if (isset($all['price'])) {
            $explodedPrice = explode('TO', str_replace(']', '', str_replace('[', '', $all['price'])));

            foreach($explodedPrice as $index => $price) {
                $explodedPrice[$index] = (double)trim($price);
            }     

            if (count($explodedPrice) > 1) {
                unset($all['price']);
                $all['price_min'] = $explodedPrice[0];
                $all['price_max'] = $explodedPrice[1];
                return $all;
            }

            $all['price'] = $explodedPrice[0];
        }
        
        return $all;
    }
}
