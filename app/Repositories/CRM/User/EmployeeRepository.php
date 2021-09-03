<?php

declare(strict_types=1);

namespace App\Repositories\CRM\User;

use App\Models\CRM\Dms\ServiceOrder;
use App\Models\CRM\Dms\ServiceOrder\ServiceItem;
use App\Models\CRM\Dms\ServiceOrder\ServiceItemTechnician;
use App\Models\CRM\Dms\ServiceOrder\Technician;
use App\Models\CRM\User\Employee;
use App\Models\User\DealerUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters): LengthAwarePaginator
    {
        $employeeTbName = Employee::getTableName();
        $technicianTbName = Technician::getTableName();

        $query = Employee::select($employeeTbName.'.*');

        if (!isset($filters['per_page'])) {
            $filters['per_page'] = 100;
        }

        $query->leftJoin(DealerUser::TABLE_NAME, 'dealer_user_id', '=', 'crm_user_id');
        $query->leftJoin($technicianTbName, $technicianTbName.'.id', '=', 'service_user_id');

        if (isset($filters['dealer_id'])) {
            $query->where($employeeTbName.'.dealer_id', $filters['dealer_id']);
        }

        if (isset($filters['dealer_user_id'])) {
            $query->where('dealer_user_id', $filters['dealer_user_id']);
        }

        if (isset($filters['is_timeclock_user'])) {
            $query->where('is_timeclock_user', true);
        }

        return $query->paginate($filters['per_page'])->appends($filters);
    }

    public function getLaborDetails(array $params): Collection
    {
        $employeeTbName = Employee::getTableName();
        $technicianTbName = Technician::getTableName();
        $serviceOrderTbName = ServiceOrder::getTableName();
        $serviceTechTbName = ServiceItemTechnician::getTableName();
        $serviceItemTbName = ServiceItem::getTableName();
        $laborCodeTbName = ServiceOrder\LaborCode::getTableName();

        $query = Employee::select(
            [
                $serviceOrderTbName . '.user_defined_id' ,
                $serviceTechTbName.'.paid_hrs',
                $serviceTechTbName.'.billed_hrs',
                $serviceTechTbName.'.start_date',
                $serviceTechTbName.'.completed_date',
                $laborCodeTbName. '.name as labor_code',
                $technicianTbName. '.hourly_rate'
            ]
        );

        $query->leftJoin($technicianTbName, $technicianTbName.'.id', '=', 'service_user_id');
        $query->leftJoin($serviceTechTbName, $employeeTbName . '.service_user_id', '=', $serviceTechTbName . '.dms_settings_technician_id');
        $query->leftJoin($serviceItemTbName, $serviceTechTbName.'.service_item_id', '=', $serviceItemTbName . '.id');
        $query->leftJoin($serviceOrderTbName, $serviceOrderTbName.'.id', '=', $serviceItemTbName . '.repair_order_id');
        $query->leftJoin($laborCodeTbName, $laborCodeTbName . '.id', '=', $serviceItemTbName . '.labor_code_id');

        $query->where($serviceTechTbName . '.start_date', '>=', $params['from_date']);
        $query->where($serviceTechTbName . '.completed_date', '<=', $params['to_date']);
        $query->where($employeeTbName . '.id', '=', $params['employee_id']);

        return $query->get();
    }

    public function findWhoHasTimeClockEnabled(array $filters): LengthAwarePaginator
    {
        return $this->getAll(array_merge($filters, ['is_timeclock_user' => true]));
    }

    /**
     * It can retrieve a employee by: id, crm_user_id and service_user_id
     *
     * @param  array  $filters
     * @return Employee|null
     */
    public function get(array $filters): ?Employee
    {
        $query = Employee::select('*');

        if (isset($filters['id'])) {
            return $query->where('id', $filters['id'])->first();
        }

        if (isset($filters['crm_user_id'])) {
            return $query->where('crm_user_id', $filters['crm_user_id'])->first();
        }

        if (isset($filters['service_user_id'])) {
            return $query->where('service_user_id', $filters['service_user_id'])->first();
        }

        throw new InvalidArgumentException('It was not provided any key column to retrieve an employee.');
    }
}
