<?php

namespace App\Domains\DealerExports\BackOffice\Settings;

use App\Domains\DealerExports\BaseExportAction;
use App\Contracts\DealerExports\EntityActionExportable;
use App\Models\CRM\User\Employee;
use App\Domains\DealerExports\ExportStartAction;
use App\Domains\DealerExports\ExportFinishedAction;

class EmployeesExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'employees';

    public function getQuery()
    {
        /**
         * SELECT de.*, CONCAT(dst.first_name, ' ', dst.last_name) as serviceUserName, du.email as crmUserName
                FROM dealer_employee as de
                    LEFT JOIN dealer_users as du on de.crm_user_id = du.dealer_user_id
                    LEFT JOIN dms_settings_technician as dst on de.service_user_id = dst.id
                WHERE de.dealer_id='{$this->getDealerId()}
         */
        return Employee::query()
            ->selectRaw('dealer_employee.*, CONCAT(dms_settings_technician.first_name, " ", dms_settings_technician.last_name) as service_user, dealer_users.email as crm_user')
            ->leftJoin('dealer_users', 'dealer_employee.crm_user_id', '=', 'dealer_users.dealer_user_id')
            ->leftJoin('dms_settings_technician', 'dms_settings_technician.id', '=', 'dealer_employee.service_user_id')
            ->where('dealer_employee.dealer_id', $this->dealer->dealer_id);
    }

    public function execute(): void
    {
        (new ExportStartAction($this->dealer, self::ENTITY_TYPE))->execute();

        $this->setFilename(self::ENTITY_TYPE)
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

        (new ExportFinishedAction(
            $this->dealer,
            self::ENTITY_TYPE,
            $this->storage->url($this->filename)
        ))->execute();
    }
}
