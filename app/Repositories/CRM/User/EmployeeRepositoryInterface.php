<?php

declare(strict_types=1);

namespace App\Repositories\CRM\User;

use App\Models\CRM\User\Employee;
use App\Repositories\GenericRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EmployeeRepositoryInterface extends GenericRepository
{
    /**
     * @param array $filters
     * @return Employee|null
     */
    public function get(array $filters): ?Employee;

    /**
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function find(array $filters): LengthAwarePaginator;

    /**
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function findWhoHasTimeClockEnabled(array $filters): LengthAwarePaginator;
}
