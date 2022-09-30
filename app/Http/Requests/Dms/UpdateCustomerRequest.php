<?php

namespace App\Http\Requests\Dms;

use App\Http\Requests\Request;
use App\Rules\QBO\ValidStringCharacters;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'integer|required',
            'first_name' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_SECTION)],
            'last_name' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_SECTION)],
            'display_name' => [
                'required',
                'string',
                Rule::unique('dms_customer', 'display_name')
                    ->ignore($this->get('id'))
                    ->where('dealer_id', $this->get('dealer_id'))
                    ->whereNull('deleted_at'),
                new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_SECTION),
            ],
            'email' => 'email|nullable',
            'drivers_license' => 'string|nullable',
            'home_phone' => ['string', 'nullable', 'max:20', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_PHONE_SECTION)],
            'work_phone' => ['string', 'nullable', 'max:20', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_PHONE_SECTION)],
            'cell_phone' => ['string', 'nullable', 'max:20', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_PHONE_SECTION)],
            'address' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_SECTION)],
            'city' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_SECTION)],
            'region' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_REGION_SECTION)],
            'postal_code' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_POSTAL_CODE_SECTION), 'max:20'],
            'country' => 'string|nullable',
            'website_lead_id' => 'integer|nullable',
            'tax_exempt' => 'integer|nullable',
            'is_financing_company' => 'integer|nullable',
            'account_number' => 'string|nullable',
            'qb_id' => 'integer|nullable',
            'gender' => 'string|nullable',
            'dob' => 'string|nullable',
            'deleted_at' => 'date|nullable',
            'is_wholesale' => 'integer',
            'default_discount_percent' => 'required|numeric',
            'middle_name' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_SECTION)],
            'company_name' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_SECTION)],
            'use_same_address' => 'integer',
            'shipping_address' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_SECTION)],
            'shipping_city' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_SECTION)],
            'shipping_region' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_REGION_SECTION)],
            'shipping_postal_code' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_POSTAL_CODE_SECTION), 'max:20'],
            'shipping_country' => 'string|nullable',
            'county' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_SECTION)],
            'shipping_county' => ['string', 'nullable', new ValidStringCharacters(ValidStringCharacters::CUSTOMERS_SECTION)],
        ];
    }
}
