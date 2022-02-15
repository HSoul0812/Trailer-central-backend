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
        'sort' => 'in:price,-price,relevance,title,-title,length,-length,sku,-sku,dealer_cost,-dealer_cost,msrp,-msrp,subcategory,-subcategory,created_at,-created_at,stock,-stock',
        'type_id' => 'array',
        'type_id.*' => 'type_exists',
        'category_id' => 'array',
        'category_id.*' => 'category_exists',
        'manufacturer_id.*' => 'integer|manufacturer_exists',
        'manufacturer_id' => 'array',
        'brand_id' => 'array',
        'brand_id.*' => 'brand_exists',
        'price' => 'price_format',
        'dealer_id' => 'array',
        'dealer_id.*' => 'integer',
        'show_on_website' => 'boolean',
        'sku' => 'min:1|sku_type',
        'id' => 'array',
        'id.*' => 'integer',
        'is_sublet_specfic' => 'integer'
    ];

    public function all($keys = null) {
        $all = parent::all($keys);

        if (isset($all['price'])) {
            if (isset($all['price']['gt']) || isset($all['price']['lt'])) {
                return $all;
            }

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
