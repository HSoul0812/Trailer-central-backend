<?php

namespace App\Transformers\Dms;

use League\Fractal\TransformerAbstract;

class CustomerTransformer extends TransformerAbstract
{

    public function transform($customer)
    {
        return [
            'id' => (int)$customer->id,
            'dealer_id' => (int)$customer->dealer_id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'display_name' => $customer->display_name ?? "{$customer->first_name} {$customer->last_name}",
            'email' => $customer->email,
            'drivers_license' => $customer->drivers_license,
            'home_phone' => $customer->home_phone,
            'work_phone' => $customer->work_phone,
            'cell_phone' => $customer->cell_phone,
            'address' => $customer->address,
            'city' => $customer->city,
            'region' => $customer->region,
            'postal_code' => $customer->postal_code,
            'country' => $customer->country,
            'website_lead_id' => (int)$customer->website_lead_id,
            'tax_exempt' => (int)$customer->tax_exempt,
            'is_financing_company' => (int)$customer->is_financing_company,
            'account_number' => $customer->account_number,
            'qb_id' => (int)$customer->qb_id,
            'gender' => $customer->gender,
            'dob' => $customer->dob,
            'deleted_at' => $customer->deleted_at,
            'is_wholesale' => (int)$customer->is_wholesale,
            'default_discount_percent' => (float)$customer->default_discount_percent,
            'middle_name' => $customer->middle_name,
            'company_name' => $customer->company_name,
            'use_same_address' => (int)$customer->use_same_address,
            'shipping_address' => $customer->shipping_address,
            'shipping_city' => $customer->shipping_city,
            'shipping_region' => $customer->shipping_region,
            'shipping_postal_code' => $customer->shipping_postal_code,
            'shipping_country' => $customer->shipping_country,
            'county' => $customer->county,
            'shipping_county' => $customer->shipping_county,
        ];
    }
}
