<?php

namespace App\Domains\DealerExports\BackOffice\Settings;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use App\Models\CRM\User\Employee;

/**
 * Class EmployeesExportAction
 *
 * @package App\Domains\DealerExports\BackOffice\Settings
 */
class EmployeesExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'employees';

    public function getQuery()
    {
        return Employee::query()
            ->selectRaw('dealer_employee.*, CONCAT(dms_settings_technician.first_name, " ", dms_settings_technician.last_name) as service_user, dealer_users.email as crm_user')
            ->leftJoin('dealer_users', 'dealer_employee.crm_user_id', '=', 'dealer_users.dealer_user_id')
            ->leftJoin('dms_settings_technician', 'dms_settings_technician.id', '=', 'dealer_employee.service_user_id')
            ->where('dealer_employee.dealer_id', $this->dealer->dealer_id);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'first_name' => 'First Name',
                'last_name' => 'Last Nmae',
                'birthday' => 'Date of Birth',
                'address' => 'Address',
                'email' => 'Email',
                'phone' => 'Phone',
                'job_title' => 'Job Title',
                'salary' => 'Salary',
                'hourly_rate' => 'Hourly Rate',
                'commission_rate' => 'Commission Rate',
                'crm_user_id' => 'CRM User Identifier',
                'crm_user' => 'CRM User',
                'service_user_id' => 'Service User Identifier',
                'service_user' => 'Service User',
            ])
            ->export();
    }
}
