<?php

declare(strict_types=1);

namespace App\Repositories\CRM\User;

use App\Models\CRM\Dms\ServiceOrder\Technician;
use App\Models\CRM\User\Employee;
use App\Models\User\DealerUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function find(array $filters): LengthAwarePaginator
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

    public function findWhoHasTimeClockEnabled(array $filters): LengthAwarePaginator
    {
        return $this->find(array_merge($filters, ['is_timeclock_user' => true]));
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

        throw new InvalidArgumentException('It was not provided any key column to retrieve a employee.');
    }
}
