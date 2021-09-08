<?php

declare(strict_types=1);

namespace App\Http\Requests\CRM\User;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Models\User\DealerUser;

class GetTimeClockEmployeesRequest extends TimeClockRequest
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'dealer_user_id' => 'integer|min:1|exists:dealer_users,dealer_user_id'
    ];

    /**
     * @return bool
     *
     * @throws NoObjectIdValueSetException when validateObjectBelongsToUser is set to true but getObjectIdValue is set to false
     * @throws NoObjectTypeSetException when validateObjectBelongsToUser is set to true but getObject is set to false
     */
    public function validate(): bool
    {
        if (parent::validate()) {
            // if they don't have permissions, then only can see themselves
            if ($this->hasPermission()) {
                // when the permission is granted, it only will consider the `dealer_id`
                $this->offsetUnset('dealer_user_id');
            }

            return true;
        }

        return false;
    }

    protected function getObject(): DealerUser
    {
        return new DealerUser();
    }

    protected function getObjectIdValue(): ?int
    {
        return $this->getUserId();
    }

    protected function validateObjectBelongsToUser(): bool
    {
        // only will check this rule when `dealer_user_id` was provided
        return (bool) $this->getUserId();
    }
}
