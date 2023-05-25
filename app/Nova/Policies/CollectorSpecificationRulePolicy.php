<?php

namespace App\Nova\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class CollectorSpecificationRule
 * @package App\Nova\Policies
 */
class CollectorSpecificationRulePolicy extends PolicyManager
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

