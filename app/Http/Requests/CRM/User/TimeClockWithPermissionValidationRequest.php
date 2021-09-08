<?php

declare(strict_types=1);

namespace App\Http\Requests\CRM\User;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Models\CRM\User\Employee;
use App\Repositories\CRM\User\EmployeeRepositoryInterface;

abstract class TimeClockWithPermissionValidationRequest extends TimeClockRequest
{
    /**
     * Performs those defined rules and will check if the user has permissions to perform the request
     *
     * @return bool
     *
     * @throws NoObjectIdValueSetException when validateObjectBelongsToUser is set to true but getObjectIdValue is set to false
     * @throws NoObjectTypeSetException when validateObjectBelongsToUser is set to true but getObject is set to false
     */
    public function validate(): bool
    {
        if (parent::validate()) {

            if ($this->hasPermission()) {
                return true;
            }

            return $this->isTheEmployeeRelatedToCurrentUser();
        }

        return false;
    }

    protected function getObject(): Employee
    {
        return new Employee();
    }

    protected function getObjectIdValue(): ?int
    {
        return $this->getEmployeeId();
    }

    protected function validateObjectBelongsToUser(): bool
    {
        // only will check this rule when `employee_id` was provided
        return (bool) $this->getEmployeeId();
    }

    private function isTheEmployeeRelatedToCurrentUser(): bool
    {
        $secondaryUserId = $this->getUserId();

        if ($secondaryUserId === null) {
            return false;
        }

        /** @var EmployeeRepositoryInterface $employeeRepo */
        $employeeRepo = app(EmployeeRepositoryInterface::class);

        $employee = $employeeRepo->get(['crm_user_id' => $secondaryUserId]);

        return $employee && $employee->id === $this->getEmployeeId();
    }
}
