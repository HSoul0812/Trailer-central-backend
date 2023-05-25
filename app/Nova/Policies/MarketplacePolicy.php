<?php
namespace App\Nova\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class Marketplace
 * @package App\Nova\Policies
 */
class MarketplacePolicy extends PolicyManager
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
