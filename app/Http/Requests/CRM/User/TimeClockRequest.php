<?php

declare(strict_types=1);

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;
use App\Models\User\DealerUser;
use App\Models\User\Interfaces\PermissionsInterface as Permissions;
use App\Models\User\User;
use Closure;

abstract class TimeClockRequest extends Request
{
    /** @var integer */
    protected $dealerId;

    /** @var integer */
    protected $userId;

    /** @var integer */
    protected $employeeId;

    public function getUserId(): ?int
    {
        if ($this->userId === null) {
            $value = $this->input('dealer_user_id');

            $this->userId = $value ? (int) $value : null;
        }

        return $this->userId;
    }

    public function getDealerId(): ?int
    {
        if ($this->dealerId === null) {
            $value = $this->input('dealer_id');

            $this->dealerId = $value ? (int) $value : null;
        }

        return $this->dealerId;
    }

    public function getEmployeeId(): ?int
    {
        if ($this->employeeId === null) {
            $value = $this->input('employee_id');

            $this->employeeId = $value ? (int) $value : null;
        }

        return $this->employeeId;
    }

    public function hasPermission(): bool
    {
        return $this->user()->hasPermission(Permissions::TIME_CLOCK, Permissions::SUPER_ADMIN_PERMISSION);
    }

    public function getUserResolver(): Closure
    {
        return function () {
            if ($this->getUserId() === null && $this->getDealerId() === null) {
                return null;
            }

            if ($this->getUserId()) {
                return DealerUser::find($this->getUserId());
            }

            return User::find($this->getDealerId());
        };
    }
}
