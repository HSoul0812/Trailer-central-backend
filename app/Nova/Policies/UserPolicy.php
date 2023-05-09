<?php

namespace App\Nova\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;


/**
 * Class UserPolicy
 * @package App\Nova\Policies
 */
class UserPolicy extends PolicyManager
{
    use HandlesAuthorization;

    /**
     * @var array
     */
    private const VALID_ROLES = ['Admin'];

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct(
            self::VALID_ROLES
        );
    }
}
