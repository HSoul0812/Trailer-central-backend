<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;


/**
 * Class LocationPolicy
 * @package App\Nova\Policies
 */
class LocationPolicy extends PolicyManager
{
    use HandlesAuthorization;

    /**
     * @var array
     */
    private const VALID_ROLES = ['Admin', 'Support', 'Sales', 'DataSupport'];

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct(
            self::VALID_ROLES
        );
    }

    /**
     * {@inheritDoc}
     */
    public function create(NovaUser $user): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function update(NovaUser $user, $model): bool
    {
        return false;
    }
}
