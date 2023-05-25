<?php

namespace App\Nova\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class QuickbookApprovalPolicy
 * @package App\Nova\Policies
 */
class QuickbookApprovalPolicy extends PolicyManager
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
}

