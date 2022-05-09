<?php

namespace App\Domains\QuickBooks\Actions;

use App\Models\CRM\User\Customer;

class CreateAddCustomerQbObjFromCustomerAction
{
    public function execute(Customer $customer): array
    {
        return [
            'BillAddr' => [
                'Line1' => $customer->address,
                'Line2' => null,
                'City' => $customer->city,
                'Country' => $customer->country,
                'PostalCode' => $customer->postal_code,
                'CountrySubDivisionCode' => $customer->region
            ],
            'ShipAddr' => [
                'Line1' => $customer->shipping_address,
                'Line2' => null,
                'City' => $customer->shipping_city,
                'Country' => $customer->shipping_country,
                'PostalCode' => $customer->shipping_postal_code,
                'CountrySubDivisionCode' => $customer->shipping_region
            ],
            'Mobile' => [
                'FreeFormNumber' => $customer->cell_phone
            ],
            'PrimaryPhone' => [
                'FreeFormNumber' => $customer->work_phone
            ],
            'AlternatePhone' => [
                'FreeFormNumber' => $customer->home_phone
            ],
            'GivenName' => $customer->first_name,
            'MiddleName' => $customer->middle_name,
            'FamilyName' => $customer->last_name,
            'DisplayName' => $customer->display_name,
            'CompanyName' => $customer->company_name,
            'FullyQualifiedName' => $customer->display_name,
            'PrimaryEmailAddr' => [
                'Address' => $customer->email
            ]
        ];
    }
}
