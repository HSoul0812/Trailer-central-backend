<?php

namespace App\Nova\Policies;

use App\Models\User\NovaUser;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class TransactionExecuteQueuePolicy
 * @package App\Nova\Policies
 */
class TransactionExecuteQueuePolicy extends PolicyManager
{
    use HandlesAuthorization;

    /**
     * @var array
     */
    private const VALID_ROLES = ['Admin', 'Support'];

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

