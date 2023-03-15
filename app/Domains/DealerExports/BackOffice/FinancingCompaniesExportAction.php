<?php

namespace App\Domains\DealerExports\BackOffice;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use App\Models\CRM\Dms\FinancingCompany;

/**
 * Class FinancingCompaniesExportAction
 *
 * @package App\Domains\DealerExports\BackOffice
 */
class FinancingCompaniesExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'financing_companies';

    public function getQuery()
    {
        return FinancingCompany::query()->where('dealer_id', $this->dealer->dealer_id);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'first_name' => 'First Name',
                'last_name' => 'Last Name',
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
                'tax_exempt' => 'Is Tax Exempted',
                'account_number' => 'Account Number',
                'gender' => 'Gender',
                'dob' => 'Date of Birth',
                'fin' => 'FIN',
            ])
            ->export();
    }
}
