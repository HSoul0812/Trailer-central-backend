<?php

namespace App\Nova\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class ManufacturersPolicy
 * @package App\Nova\Policies
 */
class ManufacturersPolicy extends PolicyManager
{
    use HandlesAuthorization;

    /**
     * @var array
     */
    private const VALID_ROLES = ['Admin', 'Support', 'DataSupport'];

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

