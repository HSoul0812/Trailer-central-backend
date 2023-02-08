<?php

namespace App\Domains\DealerExports\BackOffice;

use App\Domains\DealerExports\BaseExportAction;
use App\Contracts\DealerExports\EntityActionExportable;
use App\Models\CRM\User\Customer;

class CustomersExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'customers';

    public function getQuery()
    {
        return Customer::query()->where('dealer_id', $this->dealer->dealer_id);
    }

    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'first_name' => 'First Name',
                'last_name' => 'Last Name',
                'middle_name' => 'Middle Name',
                'display_name' => 'Display Name',
                'email' => 'Email',
                'drivers_license' => 'Drivers License',
                'home_phone' => 'Home Phone',
                'work_phone' => 'Work Phone',
                'cell_phone' => 'Cell Phone',
                'address' => 'Address',
                'city' => 'City',
                'region' => 'Region',
                'postal_code' => 'Postal Code',
                'country' => 'Country',
                'county' => 'County',
                'tax_exempt' => 'Is Tax Exempted',
                'account_number' => 'Account Number',
                'gender' => 'Gender',
                'dob' => 'Date of Birth',
                'company_name' => 'Company Name',
                'shipping_address' => 'Shipping Address',
                'shipping_city' => 'Shipping City',
                'shipping_region' => 'Shipping Region',
                'shipping_postal_code' => 'Shipping Postal Code',
                'shipping_country' => 'Shipping Country',
                'shipping_county' => 'Shipping County',
            ])
            ->export();
    }
}
